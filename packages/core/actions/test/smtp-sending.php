<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Mail;
use equal\email\Email;

[$params, $providers] = eQual::announce([
    'description'   => 'Send a test email using current SMTP configuration',
    'constants'     => [
        'EMAIL_SMTP_HOST',
        'EMAIL_SMTP_PORT',
        'EMAIL_SMTP_ACCOUNT_USERNAME',
        'EMAIL_SMTP_ACCOUNT_PASSWORD',
        'EMAIL_SMTP_ACCOUNT_EMAIL',
        'EMAIL_SMTP_ACCOUNT_DISPLAYNAME'
        // EMAIL_SMTP_ENCRYPT is optional (handled by Mail::provideMailer if defined)
    ],
    'params'        => [
        'to' => [
            'type'        => 'string',
            'usage'       => 'email',
            'required'    => true,
            'description' => 'Recipient email address.'
        ],
        'subject' => [
            'type'        => 'string',
            'default'     => '',
            'description' => 'Optional subject override.'
        ],
        'message' => [
            'type'        => 'string',
            'default'     => '',
            'description' => 'Optional plain message (will be wrapped in a minimal HTML body).'
        ],
        'reply_to' => [
            'type'        => 'string',
            'usage'       => 'email',
            'default'     => '',
            'description' => 'Optional Reply-To address.'
        ]
    ],
    'providers'     => ['context']
]);


// Validate recipient email
if(filter_var($params['to'], FILTER_VALIDATE_EMAIL) === false) {
    throw new Exception('invalid_recipient_email', EQ_ERROR_INVALID_PARAM);
}

$from = constant('EMAIL_SMTP_ACCOUNT_EMAIL');
$to = $params['to'];

// Build subject
$subject = trim((string) $params['subject']);
if($subject === '') {
    $subject = sprintf('eQual SMTP test (%s)', date('Y-m-d H:i:s'));
}

// Build minimal HTML body
$userMessage = trim((string) $params['message']);
$body = ''
    . '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.4">'
    . '<p><strong>SMTP test email</strong></p>'
    . '<ul>'
    . '<li><strong>From:</strong> ' . htmlspecialchars($from, ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li><strong>To:</strong> ' . htmlspecialchars($to, ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li><strong>Host:</strong> ' . htmlspecialchars(constant('EMAIL_SMTP_HOST'), ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li><strong>Port:</strong> ' . htmlspecialchars((string) constant('EMAIL_SMTP_PORT'), ENT_QUOTES, 'UTF-8') . '</li>'
    . '<li><strong>Time:</strong> ' . htmlspecialchars(date('c'), ENT_QUOTES, 'UTF-8') . '</li>'
    . '</ul>';

if($userMessage !== '') {
    $body .= '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8')) . '</p>';
}

$body .= '<p style="color:#666">This message was sent by an eQual diagnostic controller.</p>'
    . '</div>';

// Create email
$email = new Email();
$email->to = $to;
$email->subject = $subject;
$email->body = $body;
$email->{'content-type'} = 'text/html';

// Optional reply-to
if(isset($params['reply_to']) && strlen(trim((string) $params['reply_to'])) > 0) {
    if(filter_var($params['reply_to'], FILTER_VALIDATE_EMAIL) === false) {
        throw new Exception('invalid_reply_to_email', EQ_ERROR_INVALID_PARAM);
    }
    $email->reply_to = trim((string) $params['reply_to']);
}

// Send immediately (skip spool)
$mailId = Mail::send($email);

// Read persisted status/response (best effort)
$mail = Mail::id($mailId)->read(['id', 'status', 'response_status', 'response'])->first(true);

$providers['context']
    ->httpResponse()
    ->body([
        'mail_id'         => $mailId,
        'status'          => $mail['status'] ?? 'sent',
        'response_status' => $mail['response_status'] ?? 250,
        'response'        => $mail['response'] ?? ''
    ])
    ->send();
