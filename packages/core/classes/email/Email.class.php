<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\email;

use equal\orm\Model;
use equal\orm\usages\UsageEmail;


class Email extends Model {

    public function getTable() {
        return 'core_mail';
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'subject'
            ],

            'message_id' => [
                'type'              => 'string',
                'description'       => "Unique string identifier of the message as per RFC 5322.",
                // 'unique'            => true
            ],

            'date' => [
                'type'              => 'datetime',
                'description'       => 'Date and time of the email message.',
                'default'           => function() { return time(); }
            ],

            'direction' => [
                'type'              => 'string',
                'selection'         => ['outgoing', 'incoming'],
                'default'           => 'outgoing',
                'description'       => 'Direction of the email message.',
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
                'usage'             => 'text/plain:1024',
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
        static $smtp_codes = [
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
