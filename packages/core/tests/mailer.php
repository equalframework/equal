<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\email\EmailAttachment;
use equal\email\EmailMessage;
use equal\mailer\Mailer;
use equal\mailer\MailerSmtp;

$get_smtp_mailer = static function(Mailer $mailer): MailerSmtp {
    $mailer_property = new ReflectionProperty(Mailer::class, 'mailer');
    $mailer_property->setAccessible(true);
    return $mailer_property->getValue($mailer);
};

$get_swift_mailer = static function(MailerSmtp $mailer): Swift_Mailer {
    $swift_mailer = new ReflectionProperty(MailerSmtp::class, 'swiftMailer');
    $swift_mailer->setAccessible(true);
    return $swift_mailer->getValue($mailer);
};

$tests = [
    '90101' => [
        'description' => "Create an SMTP mailer using the configured connection parameters.",
        'act' => function() use ($get_smtp_mailer, $get_swift_mailer) {
            $smtp_mailer = $get_smtp_mailer(Mailer::create('smtp'));
            $transport = $get_swift_mailer($smtp_mailer)->getTransport();
            $from_email = new ReflectionProperty(MailerSmtp::class, 'from_email');
            $from_email->setAccessible(true);

            return [
                'mailer' => $smtp_mailer instanceof MailerSmtp,
                'host' => $transport->getHost() === constant('EMAIL_SMTP_HOST'),
                'port' => $transport->getPort() === (int) constant('EMAIL_SMTP_PORT'),
                'username' => $transport->getUsername() === constant('EMAIL_SMTP_ACCOUNT_USERNAME'),
                'password' => $transport->getPassword() === constant('EMAIL_SMTP_ACCOUNT_PASSWORD'),
                'encryption' => $transport->getEncryption() === constant('EMAIL_SMTP_ENCRYPT'),
                'from' => $from_email->getValue($smtp_mailer) === constant('EMAIL_SMTP_ACCOUNT_EMAIL')
            ];
        },
        'assert' => function($result) {
            return $result === [
                'mailer' => true,
                'host' => true,
                'port' => true,
                'username' => true,
                'password' => true,
                'encryption' => true,
                'from' => true
            ];
        }
    ],
    '90102' => [
        'description' => "Create an SMTP mailer using explicit connection options.",
        'act' => function() use ($get_smtp_mailer, $get_swift_mailer) {
            $mailer = Mailer::create('smtp', [
                'host' => 'smtp.test.local',
                'port' => '1025',
                'username' => 'test-user',
                'password' => 'test-password'
            ]);
            $transport = $get_swift_mailer($get_smtp_mailer($mailer))->getTransport();

            return [
                'host' => $transport->getHost() === 'smtp.test.local',
                'port' => $transport->getPort() === 1025,
                'username' => $transport->getUsername() === 'test-user',
                'password' => $transport->getPassword() === 'test-password'
            ];
        },
        'assert' => function($result) {
            return $result === [
                'host' => true,
                'port' => true,
                'username' => true,
                'password' => true
            ];
        }
    ],
    '90103' => [
        'description' => "Reject the removed API mailer transport.",
        'act' => function() {
            try {
                Mailer::create('api');
            }
            catch(Exception $e) {
                return $e->getMessage();
            }
            return null;
        },
        'assert' => function($result) {
            return $result === 'unsupported_mailer_type';
        }
    ],
    '90104' => [
        'description' => "Delegate an EmailMessage and send options through the Mailer facade.",
        'act' => function() {
            $email = (new EmailMessage())
                ->setTo('recipient@example.com')
                ->setSubject('Test');
            $options = ['from' => 'sender@example.com'];
            $transport = new class {
                public ?EmailMessage $email = null;
                public array $options = [];

                public function send(EmailMessage $email, array $options = []): int {
                    $this->email = $email;
                    $this->options = $options;
                    return 1;
                }
            };
            $mailer = Mailer::create('smtp');
            $mailer_property = new ReflectionProperty(Mailer::class, 'mailer');
            $mailer_property->setAccessible(true);
            $mailer_property->setValue($mailer, $transport);

            return [
                'sent' => $mailer->send($email, $options),
                'email' => $transport->email === $email,
                'options' => $transport->options === $options
            ];
        },
        'assert' => function($result) {
            return $result === [
                'sent' => 1,
                'email' => true,
                'options' => true
            ];
        }
    ],
    '90105' => [
        'description' => "Build an SMTP envelope with recipients, HTML and a raw attachment without sending it.",
        'act' => function() use ($get_smtp_mailer) {
            $email = (new EmailMessage())
                ->setTo('recipient@example.com')
                ->setReplyTo('reply@example.com')
                ->addCc('cc@example.com')
                ->addBcc('bcc@example.com')
                ->setSubject('SMTP envelope')
                ->setContentType('text/html')
                ->setBody('<p>Hello</p>');
            $create_envelope = new ReflectionMethod(MailerSmtp::class, 'createEnvelope');
            $create_envelope->setAccessible(true);
            $smtp_mailer = $get_smtp_mailer(Mailer::create('smtp'));
            $html_envelope = $create_envelope->invoke($smtp_mailer, $email->toArray(false));
            $email->addAttachment(new EmailAttachment('document.txt', 'raw attachment', 'text/plain'));
            $envelope = $create_envelope->invoke(
                $smtp_mailer,
                $email->toArray(false)
            );
            $attachments = $envelope->getChildren();

            return [
                'to' => array_key_exists('recipient@example.com', $envelope->getTo()),
                'cc' => array_key_exists('cc@example.com', $envelope->getCc()),
                'bcc' => array_key_exists('bcc@example.com', $envelope->getBcc()),
                'reply_to' => array_key_exists('reply@example.com', $envelope->getReplyTo()),
                'subject' => $envelope->getSubject(),
                'html_content_type' => $html_envelope->getContentType(),
                'attachment_content_type' => $envelope->getContentType(),
                'body' => $envelope->getBody(),
                'attachment_count' => count($attachments),
                'attachment_name' => $attachments[0]->getFilename(),
                'attachment_type' => $attachments[0]->getContentType(),
                'attachment_body' => $attachments[0]->getBody()
            ];
        },
        'assert' => function($result) {
            return $result === [
                'to' => true,
                'cc' => true,
                'bcc' => true,
                'reply_to' => true,
                'subject' => 'SMTP envelope',
                'html_content_type' => 'text/html',
                'attachment_content_type' => 'multipart/mixed',
                'body' => '<p>Hello</p>',
                'attachment_count' => 1,
                'attachment_name' => 'document.txt',
                'attachment_type' => 'text/plain',
                'attachment_body' => 'raw attachment'
            ];
        }
    ],
    '90106' => [
        'description' => "Build a plain-text SMTP envelope without sending it.",
        'act' => function() use ($get_smtp_mailer) {
            $email = (new EmailMessage())
                ->setTo('recipient@example.com')
                ->setSubject('Plain text')
                ->setContentType('text/plain')
                ->setBody('Hello');
            $create_envelope = new ReflectionMethod(MailerSmtp::class, 'createEnvelope');
            $create_envelope->setAccessible(true);
            $envelope = $create_envelope->invoke(
                $get_smtp_mailer(Mailer::create('smtp')),
                $email->toArray(false)
            );

            return [
                'content_type' => $envelope->getContentType(),
                'body' => $envelope->getBody()
            ];
        },
        'assert' => function($result) {
            return $result === [
                'content_type' => 'text/plain',
                'body' => 'Hello'
            ];
        }
    ],
    '90107' => [
        'description' => "Keep the core Mail sending and queueing entry points available.",
        'act' => function() {
            $methods = [];
            foreach(['send', 'sendRaw', 'queue', 'flush'] as $method_name) {
                $method = new ReflectionMethod(core\Mail::class, $method_name);
                $methods[$method_name] = $method->isPublic() && $method->isStatic();
            }
            return $methods;
        },
        'assert' => function($result) {
            return $result === [
                'send' => true,
                'sendRaw' => true,
                'queue' => true,
                'flush' => true
            ];
        }
    ]
];
