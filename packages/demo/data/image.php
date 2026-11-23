<?php
use equal\http\HttpRequest;

/**
 * HTTP native support
 *
 */
[$params, $providers] = eQual::announce([
    'description'   => 'Get picture data from imgur.com using imgur API.',
    'params'        => [
        'id' => [
            'type'          => 'string',
            'description'   => 'Hash of the image to retrieve',
            'default'       => 'a5NE1kW'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$request = new httpRequest("GET https://api.imgur.com/3/image/{$params['id']}");

$response = $request
            ->header('Authorization', "Client-ID 34030ab1f5ef12d")
            ->send();

$context
    ->httpResponse()
    ->body(['result' => $response->body()])
    ->send();
