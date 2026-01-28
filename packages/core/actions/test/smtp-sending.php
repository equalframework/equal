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
        'EMAIL_SMTP_ACCOUNT_EMAIL'
    ],
    'params'        => [
        'to' => [
            'type'        => 'string',
            'usage'       => 'email',
            'required'    => true,
            'description' => 'Recipient email address.'
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
$subject = sprintf('eQual SMTP test (%s)', date('Y-m-d H:i:s'));

// Build minimal HTML body
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

$body .= '<p style="color:#666">This message was sent by an eQual diagnostic controller.</p>'
    . '</div>';

// Create email
$email = new Email();
$email->setTo($to);
$email->setSubject($subject);
$email->setBody($body);

// Send (no DB write)
$sent = Mail::sendRaw($email);

$providers['context']
    ->httpResponse()
    ->body([
        'status' => ($sent > 0) ? 'sent' : 'failed',
    ])
    ->send();
