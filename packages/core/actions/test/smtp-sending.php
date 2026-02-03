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
        'EMAIL_SMTP_ACCOUNT_EMAIL'
    ],
    'params'        => [
        'to' => [
            'type'        => 'string',
            'usage'       => 'email',
            'required'    => true,
            'description' => 'Recipient email address.'
        ],
        'smtp_host' => [
            'type'        => 'string',
            'description' => 'Override SMTP host for this test only.'
        ],
        'smtp_port' => [
            'type'        => 'integer',
            'description' => 'Override SMTP port for this test only.'
        ],
        'smtp_username' => [
            'type'        => 'string',
            'description' => 'Override SMTP username for this test only.'
        ],
        'smtp_password' => [
            'type'        => 'string',
            'description' => 'Override SMTP password for this test only.'
        ],
        'smtp_from' => [
            'type'        => 'string',
            'usage'       => 'email',
            'description' => 'Override SMTP FROM address (if set, must match smtp_username).'
        ]
    ],
    'providers'     => ['context']
]);


// Validate recipient email
if(filter_var($params['to'], FILTER_VALIDATE_EMAIL) === false) {
    throw new Exception('invalid_recipient_email', EQ_ERROR_INVALID_PARAM);
}

$to = $params['to'];
$from = constant('EMAIL_SMTP_ACCOUNT_EMAIL');

$smtp_username = $params['smtp_username'] ?? constant('EMAIL_SMTP_ACCOUNT_USERNAME');

// Optional SMTP FROM validation
if(isset($params['smtp_from']) && $params['smtp_from'] !== '') {

    if($params['smtp_from'] !== $smtp_username) {
        throw new Exception('smtp_from_must_match_username', EQ_ERROR_INVALID_PARAM);
    }

    $from = $params['smtp_from'];
}

if(filter_var($from, FILTER_VALIDATE_EMAIL) === false) {
    throw new Exception('invalid_sender_email', EQ_ERROR_INVALID_PARAM);
}

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

$options = [];

if(isset($params['smtp_host']) && $params['smtp_host'] !== '') {
    $options['host'] = $params['smtp_host'];
}

if(isset($params['smtp_port']) && $params['smtp_port'] > 0) {
    $options['port'] = $params['smtp_port'];
}

if(isset($params['smtp_username']) && $params['smtp_username'] !== '') {
    $options['username'] = $params['smtp_username'];
}

if(isset($params['smtp_password']) && $params['smtp_password'] !== '') {
    $options['password'] = $params['smtp_password'];
}

// Send (no DB write)
$sent = Mail::sendRaw($email, $options);

$providers['context']
    ->httpResponse()
    ->body([
        'status' => ($sent > 0) ? 'sent' : 'failed',
        'time'   => date('c')
    ])
    ->send();
