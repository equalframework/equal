<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns schema of available types and related possible attributes.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'params'        => [],
    'providers'     => ['context', 'orm']
]);


list($context, $orm) = [$providers['context'], $providers['orm']];

// length, 0 = no length, 1 = integer, 2 = precision.scale
$usage = [
    'amount' => [
        'money',
        'percent' => ['length' => 2],
        'rate' => ['length' => 2]
    ],

    'array'         => [
        ''
    ],

    'binary'        => [
        ''
    ],

    'color' => [
        'css',
        'rgb',
        'rgba',
        'hexadecimal'
    ],

    'country' => [
        'iso3166' => ['selection' => [
            'default' => '2',
            'selection' => ['2', '3']
        ]]
    ],


    'coordinate' => [
        'latitude' => [
            'default' => 'decimal',
            'selection' => ['decimal', 'dms']
        ],
        'longitude' => [
            'default' => 'dms',
            'selection' => ['decimal', 'dms']
        ]
    ],

    'currency' => [
        'iso-4217' => [
            'default' => 'alpha',
            'selection' => ['alpha', 'numeric']
        ]
    ],

    'date' => [
        'plain',
        'time',
        'year',
        'month',
        'weekday',
        'yearweek',
        'yearday'
    ],

    'email' => [
        ''
    ],

    'file'          => [
        'javascript',
        'pdf',
        'sql',
        'zip'
    ],

    'hash' => [
        'md2',
        'md4',
        'md5',
        'md6',
        'sha1',
        'sha256',
        'sha512'
    ],

    'image' => [
        'jpeg',
        'gif',
        'png',
        'tiff',
        'webp',
        'ief',
        'svg+xml'
    ],

    'language' => [
        'iso639' => ['selection' => [
            'default' => '1',
            'selection' => ['1', '2']
        ]]
    ],

    'number' => [
        'boolean' => ['length' => 1],
        'natural' => ['length' => 1],
        'integer' => [
            'default' => 'decimal',
            'selection' => ['decimal', 'hexadecimal', 'octal'],
            'length' => 1
        ],
        'real' => ['length' => 2]
    ],

    'password' => [
        'nist', 'enisa'
    ],

    'phone'         => [
        'iso-29172'
    ],

    'text' => [
        'xml',
        'html',
        'markdown',
        'wiki',
        'plain' => [
            'default' => 'short',
            'selection' => ['short', 'small', 'medium', 'long'],
            'length' => 1
        ]
    ],

    'time' => [
        'plain'
    ],

    'uri' => [
        'mailto',
        'payto' => [
            'default' => 'iban',
            'selection' => ['iban'],
            'length' => 1
        ],
        'tel',
        'http',
        'ftp',
        'isbn',
        'isan',
        'iban',
        'ean'
    ]
];

$context->httpResponse()
    ->body($usage)
    ->send();
