<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

use equal\email\EmailMessage;

class Mailer {

    private ?object $mailer = null;

    /**
     * Create a front mailer that delegates to the concrete implementation.
     *
     * @param string $type ('smtp')
     * @param array{
     *     host?: string,
     *     port?: string,
     *     username?: string,
     *     password?: string
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
     * @param EmailMessage $email
     * @param array{
     *     from?: string,
     *     username?: string
     * } $options
     * @return int
     * @throws \Exception
     */
    public function send(EmailMessage $email, $options = []): int {
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
        if($type !== 'smtp') {
            throw new \Exception("unsupported_mailer_type");
        }

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

        return new MailerSmtp($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encrypt, $from_email);
    }
}
