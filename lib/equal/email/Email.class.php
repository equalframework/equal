<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\email;


class Email {

    /**
     * Unique identifier of the message (relates to id field in `core\Mail`).
     * (optional)
     * @var string
     */
    public $id;

    /**
     * Email address of the main recipient.
     * @var string
     */
    public $to;

    /**
     * Email address to use as 'reply to'.
     * @var string
     */
    public $reply_to;

    /**
     * Array of one or more carbon-copy recipients email addresses.
     * @var string[]
     */
    public $cc;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $body;

    /**
     * Content Type of data stored the body (accepted: "text/plain" |"text/html")
     * @var string
     */
    public $content_type;

    /**
     * Array holding the attachments of the message.
     * zero or more attachments
     * @var EmailAttachment[]
     */
    public $attachments;

    public function __construct() {
        $this->id = 0;
        $this->to = '';
        $this->reply_to = '';
        $this->cc = [];
        $this->subject = '';
        $this->body = '';
        $this->content_type = 'text/html';
        $this->attachments = [];
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

    public function setTo($to) {
        $this->to = $to;
        return $this;
    }

    public function setReplyTo($reply_to) {
        $this->reply_to = $reply_to;
        return $this;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function setBody($body) {
        // prevent double line-break
        $this->body = str_replace('<p><br></p>', '<p>&nbsp;</p>', $body);
        return $this;
    }

    public function setContentType($content_type) {
        $this->content_type = $content_type;
        return $this;
    }

    public function addCc(string $cc) {
        $this->cc[] = $cc;
        return $this;
    }

    public function addAttachment(EmailAttachment $attachment) {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function toArray() {
        // #memo - 'from' field cannot differ from the email account used for sending (@see config file)
        $values = [
            'id'            => $this->id,
            'to'            => $this->to,
            'reply_to'      => $this->reply_to,
            'cc'            => $this->cc,
            'subject'       => $this->subject,
            'content-type'  => $this->content_type,
            'body'          => $this->body,
            'attachments'   => []
        ];
        if(count($this->attachments)) {
            foreach($this->attachments as $attachment) {
                $values['attachments'][] = [
                    'name'  => $attachment->name,
                    'type'  => $attachment->content_type,
                    'data'  => base64_encode($attachment->data)
                ];
            }
        }
        return $values;
    }

}