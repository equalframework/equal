<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

use equal\email\Email;
use Swift_Mailer;

class MailerSmtp extends Mailer {

    private ?Swift_Mailer $swiftMailer = null;

    private ?string $from_email = null;

    /**
     * @param string $host
     * @param string $port
     * @param string $username
     * @param string $password
     * @param string|null $encryption ('tls', 'ssl')
     * @param string|null $from_email
     * @return void
     */
    public function create($host, $port, $username, $password, $encryption = null, $from_email = null) {
        try {
            // load dependencies
            if(!file_exists(EQ_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php')) {
                throw new \Exception("missing_dependency", EQ_ERROR_INVALID_CONFIG);
            }
            require_once(EQ_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php');

            // setup SMTP settings
            $transport = new \Swift_SmtpTransport(
                $host,
                $port,
                $encryption
            );

            $transport
                ->setUsername($username)
                ->setPassword($password);

            if($encryption) {
                $transport->setStreamOptions([
                    'ssl' => [
                        'allow_self_signed'     => true,
                        'verify_peer'           => false,
                        /* #memo don't use in prod to avoid MITM exploits */
                        /*'verify_peer_name'    => false */
                    ]
                ]);
            }

            $this->swiftMailer = new \Swift_Mailer($transport);

            $this->from_email = $from_email;
        }
        catch(\Exception $e) {
            // #todo - log error
        }
    }

    /**
     * @param array{
     *     to: string,
     *     reply_to: ?string,
     *     cc: ?string,
     *     bcc: ?string,
     *     subject: ?string,
     *     content-type: ?string,
     *     body: ?string,
     *     attachments: ?array{
     *         name: string,
     *         type: string,
     *         data: string
     *     }[]
     * } $message
     * @param array{
     *     from: ?string,
     *     username: ?string
     * } $options
     * @return \Swift_Message|null
     */
    private function createEnvelope($message, $options = []): ?\Swift_Message {
        $envelope = null;

        try {
            // load dependencies
            if(!file_exists(EQ_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php')) {
                throw new \Exception("missing_dependency", EQ_ERROR_INVALID_CONFIG);
            }
            require_once(EQ_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php');

            $body = (isset($message['body'])) ? $message['body'] : '';
            $subject = (isset($message['subject'])) ? $message['subject'] : '';

            if(!isset($message['to']) || strlen($message['to']) <= 0) {
                throw new \Exception('empty_recipient', EQ_ERROR_INVALID_PARAM);
            }

            $from = $this->from_email;
            if(isset($options['from'], $options['username']) && $options['from'] === $options['username']) {
                $from = $options['from'];
            }

            $envelope = new \Swift_Message();
            // set sender and recipients
            $envelope
                ->setTo($message['to'])
                ->setFrom([$from => $from]);

            if(isset($message['cc'])) {
                if( (is_array($message['cc']) && count($message['cc']) > 0)
                    || (is_string($message['cc']) && strlen($message['cc']) > 0) ) {
                    $envelope->setCc($message['cc']);
                }
            }

            if(isset($message['bcc'])) {
                if( (is_array($message['bcc']) && count($message['bcc']) > 0)
                    || (is_string($message['bcc']) && strlen($message['bcc']) > 0) ) {
                    $envelope->setBcc($message['bcc']);
                }
            }

            if(isset($message['reply_to']) && strlen($message['reply_to']) > 0) {
                $envelope->setReplyTo($message['reply_to']);
            }

            // add subject
            $envelope->setSubject($subject);

            // process body according to content type
            if(isset($message['content-type']) && $message['content-type'] == 'text/html') {
                $envelope->setContentType('text/html');
                // handle embedded images, if any
                $body = preg_replace_callback('/(src="?)([^"]*)("?)/i',
                    function ($matches) use (&$envelope) {
                        $cid = $matches[2];
                        if(substr($cid, 4, 1) == ':') {
                            list($scheme, $data) = explode(':', $cid);
                            if($scheme == 'data') {
                                list($content_type, $data) = explode(';', $data);
                                list($encoding, $raw) = explode(',', $data);
                                if($encoding == 'base64') {
                                    $raw = base64_decode($raw);
                                }
                                list($type, $extension) = explode('/', $content_type);
                                $img = new \Swift_Image($raw, 'img_'.rand(1,999).'.'.$extension , $content_type);
                                $img->setDisposition('inline');
                                $cid = $envelope->embed($img);
                            }
                        }
                        return $matches[1].$cid.$matches[3];
                    },
                    $body);
            }

            // add body
            $envelope->setBody($body);

            // add attachments
            if(isset($message['attachments']) && count($message['attachments'])) {
                foreach($message['attachments'] as $key => $attachment) {
                    $envelope->attach(new \Swift_Attachment($attachment['data'], $attachment['name'], $attachment['type']));
                }
            }
        }
        catch(\Exception $e) {
            trigger_error("ORM::createEnvelope: ".$e->getMessage(), EQ_REPORT_ERROR);
        }

        return $envelope;
    }

    /**
     * @param Email $email
     * @param array{
     *     from?: string,
     *     username?: string
     * } $options
     * @return int
     * @throws \Exception
     */
    public function send($email, $options = []): int {
        if(!$this->swiftMailer) {
            throw new \Exception("mailer_not_created");
        }

        // Build a "message" array compatible with createEnvelope()
        // Email::toArray() already matches what createEnvelope expects (to, subject, body, attachments, content-type, cc, bcc, reply_to, ...)
        $message = $email->toArray();

        $enveloppe = $this->createEnvelope($message, $options);
        if(!$enveloppe) {
            throw new \Exception("failed_creating_envelope", EQ_ERROR_UNKNOWN);
        }

        return $this->swiftMailer->send($enveloppe);
    }
}
