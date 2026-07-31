<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\mailer\Mailer;
use equal\email\Email;


class Mail extends \core\email\Email {

    const MESSAGE_FOLDER = EQ_BASEDIR . '/spool';

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
        $filename = self::MESSAGE_FOLDER . '/' . md5(time() . '-' . $email->subject . '-' . $email->to);
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

        try {
            // get SMTP mailer
            $mailer = Mailer::create(constant('EMAIL_TRANSPORT') ?? 'smtp');
            if(!$mailer) {
                throw new \Exception('failed_creating_mailer', EQ_ERROR_UNKNOWN);
            }
            // send email message
            if($mailer->send($email) == 0) {
                throw new \Exception('failed_sending_email', EQ_ERROR_UNKNOWN);
            }
            trigger_error("PHP::Mail::send() successfully sent email message {$mail['id']}", EQ_REPORT_INFO);
            // update the core\Mail object status
            self::id($mail['id'])->update(['status' => 'sent', 'response_status' => 250]);
        }
        catch(\Exception $e) {
            trigger_error("PHP::Mail::send() failed: " . $e->getMessage(), EQ_REPORT_ERROR);
            self::id($mail['id'])->update(['status' => 'failing', 'response_status' => 500, 'response' => substr($e->getMessage(), 0, 1024)]);
            throw new \Exception($e->getMessage(), EQ_ERROR_UNKNOWN);
        }

        return $mail['id'];
    }

    /**
     * Send an Email directly through SMTP without creating any ORM/DB object.
     */
    public static function sendRaw(Email $email, $options=[]): int {
        try {
            $mailer = Mailer::create(constant('EMAIL_TRANSPORT') ?? 'smtp', $options);
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
        $mailer = Mailer::create(constant('EMAIL_TRANSPORT') ?? 'smtp');
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
                        unlink(self::MESSAGE_FOLDER . '/' . $file);
                        continue;
                    }
                }

                $email = self::emailFromArray($message);

                // send email
                if($mailer->send($email) == 0) {
                    throw new \Exception('failed_sending_email', EQ_ERROR_UNKNOWN);
                }

                trigger_error("APP::Mail::send() successfully sent email message {$message['id']}", EQ_REPORT_INFO);

                // upon successful sending, remove the mail from the outbox
                $filename = self::MESSAGE_FOLDER . '/' . $file;
                unlink($filename);

                // if the message is linked to a core\Mail object, update the latter's status
                if(isset($message['id'])) {
                    self::id($message['id'])->update(['status' => 'sent', 'response_status' => 250]);
                }

                // prevent flooding the SMTP (wait 1000 ms)
                usleep(1000 * 1000);
                ++$i;
            }
            catch(\Exception $e) {
                // sending failed
                trigger_error("APP::Mail::flush() failed: ".$e->getMessage(), EQ_REPORT_ERROR);
                // if the message is linked to a core\Mail object, update the latter's status
                if(isset($message['id'])) {
                    self::id($message['id'])->update(['status' => 'failing', 'response_status' => 500, 'response' => substr($e->getMessage(), 0, 1024)]);
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
            // Skip hidden and special files
            if($file !== '' && $file[0] === '.') {
                continue;
            }
            // extract message details
            $filename = self::MESSAGE_FOLDER . '/' . $file;
            $data = file_get_contents($filename);
            if(!$data) {
                // silently ignore reading errors
                continue;
            }
            $message = json_decode($data, true);
            if(!$message) {
                // silently ignore invalid messages
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
     *
     */
    protected static function createMail(Email $email, string $object_class = '', int $object_id = 0): array {
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

        // export resulting message as array with attachment data encoded in base64
        return $email->setId($mail['id'])->toArray(true);
    }

    private static function emailFromArray(array $message): Email {
        $email = new Email();

        if(isset($message['id'])) {
            $email->setId($message['id']);
        }

        if(isset($message['to'])) {
            $email->setTo($message['to']);
        }

        if(isset($message['reply_to']) && !empty($message['reply_to'])) {
            $email->setReplyTo($message['reply_to']);
        }

        if(isset($message['subject'])) {
            $email->setSubject($message['subject']);
        }

        if(isset($message['content-type']) && !empty($message['content-type'])) {
            $email->setContentType($message['content-type']);
        }

        if(isset($message['body'])) {
            $email->setBody($message['body']);
        }

        if(isset($message['cc']) && is_array($message['cc'])) {
            foreach($message['cc'] as $cc) {
                $email->addCc($cc);
            }
        }

        if(isset($message['bcc']) && is_array($message['bcc'])) {
            foreach($message['bcc'] as $bcc) {
                $email->addBcc($bcc);
            }
        }

        if(isset($message['attachments']) && is_array($message['attachments'])) {
            foreach($message['attachments'] as $attachment) {
                $email->addAttachment(new \equal\email\EmailAttachment(
                    $attachment['name'],
                    $attachment['data'],
                    $attachment['type']
                ));
            }
        }

        return $email;
    }

}
