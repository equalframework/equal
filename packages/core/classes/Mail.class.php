<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\mailer\MailerFactory;
use equal\orm\Model;
use equal\email\Email;
use equal\orm\usages\UsageEmail;


class Mail extends Model {

    const MESSAGE_FOLDER = EQ_BASEDIR.'/spool';

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'subject'
            ],

            'to' => [
                'type'              => 'string',
                'usage'             => 'email',
                'required'          => true
            ],

            'reply_to' => [
                'type'              => 'string',
                'usage'             => 'email'
            ],

            'cc' => [
                'type'              => 'string',
                'description'       => 'Comma separated list of carbon-copy recipients.',
                'generation'        => 'generateCc'
            ],

            'bcc' => [
                'type'              => 'string',
                'description'       => 'Comma separated list of blind carbon-copy recipients.',
                'generation'        => 'generateBcc'
            ],

            'subject' => [
                'type'              => 'string',
                'required'          => true
            ],

            'body' => [
                'type'              => 'string',
                'usage'             => 'text/html',
                'required'          => true
            ],

            'attachments' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'List of documents names attached to the email, comma separated (no content).',
                'generation'        => 'generateAttachments'
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => 'Class of the object object_id points to.'
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => 'Identifier of the object the email originates from.'
            ],

            'response_status' => [
                'type'              => 'integer',
                'description'       => 'SMTP response status code.',
                'visible'           => ['status', '<>', 'pending'],
                'generation'        => 'generateResponseStatus'
            ],

            'response' => [
                'type'              => 'string',
                'description'       => 'SMTP response returned at sending.',
                'default'           => '',
                'visible'           => ['status', '<>', 'pending'],
                'generation'        => 'generateResponse'
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'pending',
                    'failing',
                    'sent'
                ],
                'default'           => 'pending',
                'description'       => 'Sending status of the mail.'
            ]
        ];
    }

    /**
     * Queue a message in the email outbox (/spool).
     *
     * @param   Email   $email           Email message to be sent.
     * @param   string  $object_class    Class of the object associated with the sending (optional).
     * @param   string  $object_id       Identifier of the object associated with the sending (optional).
     *
     * @return  int     Upon success, this method returns the id of the queued `core\Mail` object.
     *
     * @throws  \Exception                This method raises an Exception in case of error.
     */
    public static function queue(Email $email, string $object_class = '', int $object_id = 0): int {
        $mail = self::createMail($email, $object_class, $object_id);

        // convert to JSON
        $data = json_encode($mail, JSON_PRETTY_PRINT);
        if($data === false) {
            throw new \Exception('failed_json_conversion', EQ_ERROR_UNKNOWN);
        }
        // export to outbox
        $filename = self::MESSAGE_FOLDER.'/'.md5(time().'-'.$email->subject.'-'.$email->to);
        if(file_put_contents($filename, $data) === false) {
            throw new \Exception('failed_file_creation', EQ_ERROR_UNKNOWN);
        }

        return $mail['id'];
    }

    /**
     * Instantly send a message (skip outbox).
     *
     * @param   Email   $email           Email message to be sent.
     * @param   string  $object_class    Class of the object associated with the sending (optional).
     * @param   string  $object_id       Identifier of the object associated with the sending (optional).
     *
     * @return  int     Upon success, this method returns the id of the created `core\Mail` object created.
     *
     * @throws  \Exception                This method raises an Exception in case of error.
     */
    public static function send(Email $email, string $object_class = '', int $object_id = 0): int {
        $mail = self::createMail($email, $object_class, $object_id);

        // ensure attachments contain binary data (createMail uses base64 for JSON safety)
        if(isset($mail['attachments']) && count($mail['attachments'])) {
            foreach($mail['attachments'] as &$attachment) {
                if(isset($attachment['data'])) {
                    $attachment['data'] = base64_decode($attachment['data']);
                }
            }
        }

        try {
            // get SMTP mailer
            $mailer = MailerFactory::create(constant('EMAIL_PROVIDER') ?? 'smtp');
            if(!$mailer) {
                throw new \Exception('failed_creating_mailer', EQ_ERROR_UNKNOWN);
            }
            // build envelope
            $envelope = self::createEnvelope($mail);
            if(!$envelope) {
                throw new \Exception('failed_creating_envelope', EQ_ERROR_UNKNOWN);
            }
            // send email message
            if($mailer->send($envelope) == 0) {
                throw new \Exception('failed_sending_email', EQ_ERROR_UNKNOWN);
            }
            trigger_error("PHP::Mail::send() successfully sent email message {$mail['id']}", EQ_REPORT_INFO);
            // update the core\Mail object status
            self::id($mail['id'])->update(['status' => 'sent', 'response_status' => 250]);
        }
        catch(\Exception $e) {
            trigger_error("PHP::Mail::send() failed: ".$e->getMessage(), EQ_REPORT_ERROR);
            self::id($mail['id'])->update(['status' => 'failing', 'response_status' => 500, 'response' => $e->getMessage()]);
            throw new \Exception($e->getMessage(), EQ_ERROR_UNKNOWN);
        }

        return $mail['id'];
    }

    /**
     * Send an Email directly through SMTP without creating any ORM/DB object.
     */
    public static function sendRaw(Email $email, $options=[]): int {
        try {
            $mailer = MailerFactory::create(constant('EMAIL_PROVIDER') ?? 'smtp', $options);
            if(!$mailer) {
                throw new \Exception('failed_creating_mailer', EQ_ERROR_UNKNOWN);
            }

            $sent = $mailer->send($email, $options);
            if($sent <= 0) {
                throw new \Exception('failed_sending_email', EQ_ERROR_UNKNOWN);
            }
        }
        catch(\Exception $e) {
            trigger_error("PHP::Mail::sendRaw() failed: ".$e->getMessage(), EQ_REPORT_ERROR);
            throw new \Exception($e->getMessage(), EQ_ERROR_UNKNOWN);
        }
        return $sent;
    }

    public static function isQueued(int $id): bool {
        $files = scandir(self::MESSAGE_FOLDER) ?: [];
        foreach($files as $file) {
            // skip special files
            if(in_array($file, ['.', '..', '.gitkeep'])) {
                continue;
            }
            // extract message details
            $filename = self::MESSAGE_FOLDER.'/'.$file;
            $data = file_get_contents($filename);
            if(!$data) {
                // ignore reading errors
                continue;
            }
            $message = json_decode($data, true);
            if(!$message) {
                // ignore invalid messages
                continue;
            }
            if($message['id'] == $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Send a batch of messages that are queued in the outbox.
     *
     */
    public static function flush() {

        // retrieve messages from files under `/spool`
        $queue = self::fetchQueue();

        // get SMTP mailer
        $mailer = MailerFactory::create(constant('EMAIL_PROVIDER') ?? 'smtp');
        if(!$mailer) {
            throw new \Exception('failed_creating_mailer', EQ_ERROR_UNKNOWN);
        }

        // #todo - store as setting
        $max = 10;
        $i = 0;
        // loop through messages
        foreach($queue as $file => $message) {
            try {
                // prevent handling more than $max messages (successfully sent)
                if($i > $max) {
                    break;
                }

                if(isset($message['id'])) {
                    $mailMessage = self::id($message['id'])->read(['status'])->first();
                    // prevent re-sending already sent messages
                    if($mailMessage['status'] == 'sent') {
                        unlink(self::MESSAGE_FOLDER.'/'.$file);
                        continue;
                    }
                }

                // send email
                if($mailer->send($message) == 0) {
                    throw new \Exception('failed_sending_email', EQ_ERROR_UNKNOWN);
                }

                trigger_error("APP::Mail::send() successfully sent email message {$message['id']}", EQ_REPORT_INFO);

                // upon successful sending, remove the mail from the outbox
                $filename = self::MESSAGE_FOLDER.'/'.$file;
                unlink($filename);

                // if the message is linked to a core\Mail object, update the latter's status
                if(isset($message['id'])) {
                    self::id($message['id'])->update(['status' => 'sent', 'response_status' => 250]);
                }

                // prevent flooding the SMTP (wait 100 ms)
                usleep(100 *1000);
                ++$i;
            }
            catch(\Exception $e) {
                // sending failed
                trigger_error("APP::Mail::flush() failed: ".$e->getMessage(), EQ_REPORT_ERROR);
                // if the message is linked to a core\Mail object, update the latter's status
                if(isset($message['id'])) {
                    self::id($message['id'])->update(['status' => 'failing', 'response_status' => 500, 'response' => $e->getMessage()]);
                }
                // #todo : add support for choosing what to do upon failure (retry, delete, notify)
            }
        }
    }

    private static function fetchQueue() {
        // load pending messages by reading all files in `$messages_folder` (outbox) directory
        $queue = [];
        $files = scandir(self::MESSAGE_FOLDER) ?: [];
        foreach($files as $file) {
            // skip special files
            if(in_array($file, ['.', '..', '.gitkeep'])) {
                continue;
            }
            // extract message details
            $filename = self::MESSAGE_FOLDER.'/'.$file;
            $data = file_get_contents($filename);
            if(!$data) {
                // ignore reading errors
                continue;
            }
            $message = json_decode($data, true);
            if(!$message) {
                // ignore invalid messages
                continue;
            }
            // convert attachments' data attributes to binary values
            if(isset($message['attachments']) && count($message['attachments'])) {
                foreach($message['attachments'] as $key => $attachment) {
                    $message['attachments'][$key]['data'] = base64_decode($attachment['data']);
                }
            }
            $queue[$file] = $message;
        }
        return $queue;
    }

    /**
     * Create a Mail object and return an associative array representation of it.
     * The Mail object is attached to an object, if provided ($object_class::$object_id).
     */
    private static function createMail(Email $email, string $object_class = '', int $object_id = 0): array {
        $values = [
            'to'            => $email->to,
            'cc'            => implode(',', (array) $email->cc),
            'bcc'           => implode(',', (array) $email->bcc),
            'subject'       => $email->subject,
            // #memo - utf8mb4 chars should be removed if DB charset does not support it
            // 'body'          => preg_replace('/(?:\xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})/xs', '', $email->body),
            'body'          => $email->body,
            'attachments'   => '',
            'object_class'  => $object_class,
            'object_id'     => $object_id
        ];

        if(isset($email->reply_to) && !empty($email->reply_to)) {
            $values['reply_to'] = $email->reply_to;
        }
        // extract attachment names, if any
        if(count($email->attachments)) {
            $attachments = array_map(function ($a) {return $a->name;}, $email->attachments);
            $values['attachments'] = implode("\n", $attachments);
        }

        // create the core\Mail object
        $mail = self::create($values)->read(['id'])->first(true);
        if(!$mail) {
            throw new \Exception('failed_creating_mail', EQ_ERROR_UNKNOWN);
        }

        // export resulting message as array
        return $email->setId($mail['id'])->toArray();
    }

    public static function generateCc(): ?string {
        return self::generateMultiEmailFieldValue();
    }

    public static function generateBcc(): ?string {
        return self::generateMultiEmailFieldValue();
    }

    private static function generateMultiEmailFieldValue(): ?string {
        $usageEmail = new UsageEmail('email');

        $qty_of_cc = mt_rand(0, 3);
        if($qty_of_cc === 0) {
            return null;
        }

        $emails = [];
        for($i = 0; $i < $qty_of_cc; $i++) {
            $emails[] = $usageEmail->generateRandomValue();
        }

        return implode(',', $emails);
    }

    public static function generateResponseStatus(): ?string {
        $smtp_codes = [
            // 2xx Success
            200 => 'Nonstandard success response',
            211 => 'System status or system help reply',
            214 => 'Help message',
            220 => 'Service ready',
            221 => 'Service closing transmission channel',
            250 => 'Requested mail action completed',
            251 => 'User not local; will forward',

            // 3xx Intermediate Replies
            354 => 'Start mail input; end with <CRLF>.<CRLF>',

            // 4xx Temporary Failures
            421 => 'Service not available, closing transmission channel',
            450 => 'Requested mail action not taken: mailbox unavailable',
            451 => 'Requested action aborted: local error in processing',
            452 => 'Requested action not taken: insufficient system storage',

            // 5xx Permanent Failures
            500 => 'Syntax error, command unrecognized',
            501 => 'Syntax error in parameters or arguments',
            502 => 'Command not implemented',
            503 => 'Bad sequence of commands',
            504 => 'Command parameter not implemented',
            550 => 'Requested action not taken: mailbox unavailable',
            551 => 'User not local; please try a different path',
            552 => 'Requested mail action aborted: exceeded storage allocation',
            553 => 'Requested action not taken: mailbox name not allowed',
            554 => 'Transaction failed',
        ];

        return $smtp_codes[mt_rand(0, count($smtp_codes) - 1)];
    }

    public static function generateResponse() {
        return null;
    }

    public static function generateAttachments(): ?string {
        $attachments = [];
        for($i = 0; $i < mt_rand(0, 3); $i++) {
            $extensions = ['pdf', 'docx', 'txt', 'xls', 'csv', 'ppt', 'jpg', 'png'];
            $attachments[] = 'document ' . mt_rand(0, 100) . '.' . $extensions[mt_rand(0, count($extensions) - 1)];
        }

        return empty($attachments) ? null : implode(',', $attachments);
    }

}
