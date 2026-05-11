<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

use equal\email\Email;

class Mailer {

    private ?object $mailer = null;

    /**
     * Create a front mailer that delegates to the concrete implementation.
     *
     * @param string $type ('smtp', 'api', 'brevo', 'sendgrid', 'postmark', 'resend', 'mailgun')
     * @param array{
     *     host?: string,
     *     port?: string,
     *     username?: string,
     *     password?: string,
     *     from?: string,
     *     auth?: array,
     *     headers?: array,
     *     success?: array,
     *     body?: array,
     *     api_url?: string,
     *     api_key?: string
     * } $options
     * @return Mailer
     */
    public static function create(string $type, array $options = []): Mailer {
        $self = new self();
        $self->mailer = self::instantiate($type, $options);
        return $self;
    }

    /**
     * Sends given email
     *
     * @param Email $email
     * @param array{
     *     from?: string,
     *     username?: string
     * } $options
     * @return int
     * @throws \Exception
     */
    public function send(Email $email, $options = []): int {
        if(!$this->mailer) {
            throw new \Exception("mailer_not_created");
        }

        return $this->mailer->send($email, $options);
    }

    /**
     * @param string $type
     * @param array $options
     * @return object
     */
    private static function instantiate(string $type, array $options): object {
        switch($type) {
            case 'smtp':
                $smtp_host = $options['host'] ?? constant('EMAIL_SMTP_HOST');
                $smtp_port = $options['port'] ?? constant('EMAIL_SMTP_PORT');
                $smtp_username = $options['username'] ?? constant('EMAIL_SMTP_ACCOUNT_USERNAME');
                $smtp_password = $options['password'] ?? constant('EMAIL_SMTP_ACCOUNT_PASSWORD');

                $smtp_encrypt = null;
                if(defined('EMAIL_SMTP_ENCRYPT') && in_array(constant('EMAIL_SMTP_ENCRYPT'), ['tls', 'ssl'])) {
                    $smtp_encrypt = constant('EMAIL_SMTP_ENCRYPT');
                }

                $from_email = null;
                if(defined('EMAIL_SMTP_ACCOUNT_EMAIL') && !empty(constant('EMAIL_SMTP_ACCOUNT_EMAIL'))) {
                    $from_email = constant('EMAIL_SMTP_ACCOUNT_EMAIL');
                }

                $mailer = new MailerSmtp($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encrypt, $from_email);
                return $mailer;

            case 'api':
                $from_email = null;
                if(defined('EMAIL_SMTP_ACCOUNT_EMAIL') && !empty(constant('EMAIL_SMTP_ACCOUNT_EMAIL'))) {
                    $from_email = constant('EMAIL_SMTP_ACCOUNT_EMAIL');
                }

                $config = [];

                if(defined('EMAIL_API_PROVIDER')) {
                    $config = self::getPreset(constant('EMAIL_API_PROVIDER'));
                }

                if(isset($options['api_url'])) {
                    $config['api_url'] = $options['api_url'];
                }

                if(isset($options['api_key'])) {
                    $config['api_key'] = $options['api_key'];
                }

                if(isset($options['from'])) {
                    $config['from'] = $options['from'];
                }
                elseif(!empty($from_email)) {
                    $config['from'] = $from_email;
                }

                if(isset($options['auth']) && is_array($options['auth'])) {
                    $config['auth'] = $options['auth'];
                }

                if(isset($options['headers']) && is_array($options['headers'])) {
                    $config['headers'] = $options['headers'];
                }

                if(isset($options['success']) && is_array($options['success'])) {
                    $config['success'] = $options['success'];
                }

                if(isset($options['body']) && is_array($options['body'])) {
                    $config['body'] = $options['body'];
                }

                $mailer = new MailerApi($config);
                return $mailer;
        }

        throw new \Exception("unsupported_mailer_type");
    }

    public static function getPreset(string $type): array {
        switch($type) {
            case 'brevo':
                return self::brevo();
            case 'sendgrid':
                return self::sendgrid();
            case 'postmark':
                return self::postmark();
            case 'resend':
                return self::resend();
            case 'mailgun':
                return self::mailgun();
            default:
                throw new \Exception("unsupported_mailer_preset");
        }

        return [];
    }

    public static function brevo(): array {
        return [
            'api_url' => 'https://api.brevo.com/v3/smtp/email',
            'auth' => [
                'type' => 'header',
                'name' => 'api-key'
            ],
            'success' => [
                'status_codes' => [201],
                'count_path' => 'messageIds'
            ],
            'body' => [
                'sender.email' => '@from',
                'subject' => '@subject',
                'to' => [
                    [
                        'email' => '@to'
                    ]
                ],
                'htmlContent' => '@body',
                '?replyTo.email' => '@reply_to',
                '?cc' => '@cc:email_list',
                '?bcc' => '@bcc:email_list'
            ]
        ];
    }

    public static function sendgrid(): array {
        return [
            'api_url' => 'https://api.sendgrid.com/v3/mail/send',
            'auth' => [
                'type' => 'bearer'
            ],
            'success' => [
                'status_codes' => [202]
            ],
            'body' => [
                'personalizations' => [
                    [
                        'to' => [
                            [
                                'email' => '@to'
                            ]
                        ],
                        '?cc' => '@cc:email_list',
                        '?bcc' => '@bcc:email_list'
                    ]
                ],
                'from.email' => '@from',
                '?from.name' => '@from_name',
                'subject' => '@subject',
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => '@body'
                    ]
                ],
                '?reply_to.email' => '@reply_to'
            ]
        ];
    }

    public static function postmark(): array {
        return [
            'api_url' => 'https://api.postmarkapp.com/email',
            'auth' => [
                'type' => 'header',
                'name' => 'X-Postmark-Server-Token'
            ],
            'success' => [
                'status_codes' => [200],
                'count_path' => 'MessageID'
            ],
            'body' => [
                'From' => '@from',
                'To' => '@to',
                '?Cc' => '@cc:email_csv',
                '?Bcc' => '@bcc:email_csv',
                'Subject' => '@subject',
                'HtmlBody' => '@body',
                '?ReplyTo' => '@reply_to'
            ]
        ];
    }

    public static function resend(): array {
        return [
            'api_url' => 'https://api.resend.com/emails',
            'auth' => [
                'type' => 'bearer'
            ],
            'success' => [
                'status_codes' => [200]
            ],
            'body' => [
                'from' => '@from',
                'to' => ['@to'],
                '?cc' => '@cc',
                '?bcc' => '@bcc',
                'subject' => '@subject',
                'html' => '@body',
                '?reply_to' => '@reply_to'
            ]
        ];
    }

    public static function mailgun(): array {
        return [
            'api_url' => 'https://api.mailgun.net/v3/YOUR_DOMAIN_NAME/messages',
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'auth' => [
                'type' => 'basic',
                'username' => 'api'
            ],
            'success' => [
                'status_codes' => [200],
                'count_path' => 'id'
            ],
            'body' => [
                'from' => '@from',
                'to' => '@to',
                '?cc' => '@cc:email_csv',
                '?bcc' => '@bcc:email_csv',
                'subject' => '@subject',
                'html' => '@body',
                '?h:Reply-To' => '@reply_to'
            ]
        ];
    }
}
