<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;
use equal\email\Email;
use equal\email\EmailAttachment;


class Mail extends Model {

    const MESSAGE_FOLDER = QN_BASEDIR.'/spool';

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'subject'
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
            ],

            'to' => [
                'type'              => 'string',
                'usage'             => 'email'
            ],

            'reply_to' => [
                'type'              => 'string',
                'usage'             => 'email'
            ],

            'cc' => [
                'type'              => 'string',
                'description'       => 'Comma separated list of carbon-copy recipients.'
            ],

            'subject' => [
                'type'              => 'string'
            ],

            'body' => [
                'type'              => 'string',
                'usage'             => 'text/html'
            ],

            'attachments' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'List of documents names attached to the email, comma separated (no content).'
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => 'Class of the object object_id points to.'
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => 'Identifier of the object the email origintates from.'
            ],

            'response_status' => [
                'type'              => 'integer',
                'description'       => 'SMTP response status code.',
                'visible'           => ['status', '<>', 'pending']
            ],

            'response' => [
                'type'              => 'string',
                'description'       => 'SMTP response returned at sending.',
                'default'           => '',
                'visible'           => ['status', '<>', 'pending']
            ]

        ];
    }

    /**
     * Add a message to the email outbox.
     * This method is a suubstitute for the create() method.
     *
     * @param   Email   $email           Email message to be sent.
     * @param   string  $object_class    Class of the object associated with the sending (optional).
     * @param   string  $object_id       Identifier of the object associated with the sending (optional).
     * @throws  Exception                This method raises an Exception in case of error.
     */
    public static function queue(Email $email, string $object_class='', int $object_id=0): void {
        // create an Object
        $values = [
            'to'            => $email->to,
            'cc'            => implode(',', $email->cc),
            'subject'       => $email->subject,
            // #todo - set DB to UTF8mb4 by default
            // remove utf8mb4 chars (emojis)
            'body'          => preg_replace('/(?:\xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})/xs', '', $email->body),
            'attachments'   => '',
            'object_class'  => $object_class,
            'object_id'     => $object_id
        ];

        if(isset($email->reply_to)) {
            $values['reply_to'] = $email->reply_to;
        }
        // extract attachment names, if any
        if(count($email->attachments)) {
            $attachments = array_map(function ($a) {return $a->name;}, $email->attachments);
            $values['attachments'] = implode("\n", $attachments);
        }
        // create the Mail object
        $mail = Mail::create($values)->read(['id'])->first(true);
        // create JSON data (append newly created object ID)
        $values = $email->setId($mail['id'])->toArray();
        // convert to JSON
        $data = json_encode($values, JSON_PRETTY_PRINT);
        if($data === false) {
            throw new Exception('failed_json_conversion', QN_ERROR_UNKNOWN);
        }
        // export to outbox
        $filename = self::MESSAGE_FOLDER.'/'.md5(time().'-'.$email->subject.'-'.$email->to);
        if(file_put_contents($filename, $data) === false) {
            throw new Exception('failed_file_creation', QN_ERROR_UNKNOWN);
        }
    }


    /**
     * Send all messages currently in the outbox.
     *
     */
    public static function flush() {
        // load dependencies
        if(!file_exists(QN_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php')) {
            throw new Exception("missing_dependency", QN_ERROR_INVALID_CONFIG);
        }

        require_once(QN_BASEDIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php');

        // load pending messages by reading all files in `$messages_folder` (outbox) directory
        $queue = [];
        $files = scandir(self::MESSAGE_FOLDER);
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

        // setup SMTP settings
        $transport = new \Swift_SmtpTransport(
            constant('EMAIL_SMTP_HOST'),
            constant('EMAIL_SMTP_PORT'),
            (defined('EMAIL_SMTP_ENCRYPT') && in_array(constant('EMAIL_SMTP_ENCRYPT'), ['tls', 'ssl']))?constant('EMAIL_SMTP_ENCRYPT'):null
        );

        $transport
            ->setUsername(constant('EMAIL_SMTP_ACCOUNT_USERNAME'))
            ->setPassword(constant('EMAIL_SMTP_ACCOUNT_PASSWORD'));

        if(defined('EMAIL_SMTP_ENCRYPT') && in_array(constant('EMAIL_SMTP_ENCRYPT'), ['tls', 'ssl'])) {
            $transport->setStreamOptions([
                'ssl' => [
                    'allow_self_signed'     => true,
                    'verify_peer'           => false
                ]
            ]);
        }

        // setup SMTP settings
        $mailer = new \Swift_Mailer($transport);

        // #todo - store as setting
        $max = 10;
        $i = 0;
        // loop through messages
        foreach($queue as $file => $message) {
            ++$i;
            // prevent handling handling more than $max messages
            if($i > $max) {
                break;
            }
            $body = (isset($message['body']))?$message['body']:'';
            $subject = (isset($message['subject']))?$message['subject']:'';

            if(isset($message['to']) && strlen($message['to']) > 0) {
                $envelope = new \Swift_Message();
                try {
                    // set from and to
                    $envelope
                        ->setTo($message['to'])
                        ->setCc($message['cc'])
                        ->setFrom([constant('EMAIL_SMTP_ACCOUNT_EMAIL') => constant('EMAIL_SMTP_ACCOUNT_DISPLAYNAME')]);

                    if(isset($message['reply_to']) && strlen($message['reply_to']) > 0) {
                        $envelope->setReplyTo($message['reply_to']);
                    }

                    // add subject
                    $envelope->setSubject($subject);
                    // add body
                    if(isset($message['content-type']) && $message['content-type'] == 'text/html') {
                        $envelope
                            ->setContentType('text/html')
                            ->setBody($body);
                    }
                    else {
                        $envelope->setBody($body);
                    }
                    // add attachments
                    if(isset($message['attachments']) && count($message['attachments'])) {
                        foreach($message['attachments'] as $key => $attachment) {
                            $envelope->attach(new \Swift_Attachment($attachment['data'], $attachment['name'], $attachment['type']));
                        }
                    }
                    // send email
                    $mailer->send($envelope);
                    // upon successful sending, remove the mail from the outbox
                    $filename = self::MESSAGE_FOLDER.'/'.$file;
                    unlink($filename);
                    // if the message is linked to a core\Mail object, update the latter's status
                    if(isset($message['id'])) {
                        self::id($message['id'])->update(['status' => 'sent', 'response_status' => 250]);
                    }
                    // prevent flooding the SMTP
                    usleep(100 *1000);
                }
                catch(\Exception $e) {
                    // sending failed:
                    // if the message is linked to a core\Mail object, update the latter's status
                    if(isset($message['id'])) {
                        self::id($message['id'])->update(['status' => 'failing', 'response_status' => 500, 'response' => $e->getMessage()]);
                    }
                }

            }

        }
    }


}