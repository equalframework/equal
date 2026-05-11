<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

class MailerFactory {

    /**
     * @param string $type ('smtp', 'brevo')
     * @param array{
     *     host?: string,
     *     port?: string,
     *     username?: string,
     *     password?: string,
     *     api_url?: string,
     *     api_key?: string
     * } $options
     * @return Mailer|null
     */
    public static function create(string $type, array $options = []): ?Mailer {
        $mailer = null;
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

                $mailer = new MailerSmtp();
                $mailer->create($smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encrypt, $from_email);
                break;
            case 'brevo':
                $brevo_api_url = $options['api_url'] ?? constant('EMAIL_BREVO_API_URL');
                $brevo_api_key = $options['api_key'] ?? constant('EMAIL_BREVO_API_KEY');

                $from_email = null;
                if(defined('EMAIL_SMTP_ACCOUNT_EMAIL') && !empty(constant('EMAIL_SMTP_ACCOUNT_EMAIL'))) {
                    $from_email = constant('EMAIL_SMTP_ACCOUNT_EMAIL');
                }

                $mailer = new MailerBrevo();
                $mailer->create($brevo_api_url, $brevo_api_key, $from_email);
                break;
        }

        return $mailer;
    }
}
