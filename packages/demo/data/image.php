<?php
use equal\http\HttpRequest;

/**
 * HTTP native support
 *
 */
list($params, $providers) = eQual::announce([
    'description'   => 'Get picture data from imgur.com using imgur API.',
    'params'        => [
        'id' => [
            'description'   => 'Hash of the image to retrieve',
            'type'          => 'string',
            'default'       => 'a5NE1kW'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

$request = new httpRequest("GET https://api.imgur.com/3/image/{$params['id']}");

$response = $request
            ->header('Authorization', "Client-ID 34030ab1f5ef12d")
            ->send();
           
$providers['context']
    ->httpResponse()
    ->body(['result' => $response->body()])
    ->send();