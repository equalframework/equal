<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\mailer;

use equal\email\Email;
use equal\http\HttpRequest;

class MailerBrevo extends Mailer {

    private ?string $api_url = null;
    private ?string $api_key = null;
    private ?string $email_from = null;

    public function create($api_url, $api_key, $email_from = null) {
        $this->api_url = $api_url;
        $this->api_key = $api_key;
        $this->email_from = $email_from;
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
        if(empty($this->api_url)) {
            throw new \Exception("missing_api_url");
        }

        if(empty($this->api_key)) {
            throw new \Exception("missing_api_key");
        }

        if(empty($options['from'] ?? $this->email_from)) {
            throw new \Exception("missing_from");
        }

        $request = new HttpRequest("POST {$this->api_url}");

        $body = [
            'sender'            => ['email' => $options['from'] ?? $this->email_from],
            'subject'           => $email->subject,
            'replyTo'           => $email->reply_to,
            'cc'                => $email->cc,
            'bcc'               => $email->bcc,
            'htmlContent'       => $email->body,
            'messageVersions'   => [
                [
                    'to' => ['email' => $email->to]
                ]
            ]
        ];

        $response = $request
            ->header('Accept', 'application/json')
            ->header('Content-Type', 'application/json')
            ->header('Api-Key', $this->api_key)
            ->setBody($body)
            ->send();

        $data = $response->body();
        $status = $response->getStatusCode();

        if($status !== 201) {
            return 0;
        }

        return 0;
    }
}
