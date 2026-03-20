<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\email\Email;
use core\Mail;

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'	=>	"Send a test email (using current config) to `EMAIL_SMTP_ACCOUNT_EMAIL`.",
    'params'        => [
        'instant' => [
            'type'              => 'boolean',
            'description'       => 'Flag for sending instant message. If false, email is queued in the spooler.',
            'default'           => true
        ]
    ],
    'constants'     => ['BACKEND_URL', 'EMAIL_SMTP_ACCOUNT_EMAIL'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

// fetch content of test message
if(!($html = @file_get_contents(EQ_BASEDIR."/packages/core/i18n/en/mail_test.html"))) {
    throw new Exception("missing_template", EQ_ERROR_INVALID_CONFIG);
}

// parse template
$body = (function ($template, $map_values) {
        foreach ($map_values as $key => $value) {
            $pattern = '/{{\s*' . preg_quote($key) . '\s*}}/';
            $template = preg_replace($pattern, $value, $template);
        }
        return $template;
    })($html, [
        'url' => constant('BACKEND_URL')
    ]);

// create message
$message = new Email();

$message->setTo(constant('EMAIL_SMTP_ACCOUNT_EMAIL'))
    ->setSubject('Test Email from eQual')
    ->setContentType("text/html")
    ->setBody($body);

if($params['instant']) {
    // send instant message
    Mail::send($message);
}
else {
    // put the message in the outgoing queue
    Mail::queue($message);
}

$context->httpResponse()
        ->status(204)
        ->send();
