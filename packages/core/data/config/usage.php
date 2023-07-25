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
    'language'      => [
        'iso639' => ['selection' => [
            'default' => '1',
            'selection' => ['1', '2']
        ]]
    ],

    'country'       => [
        'iso3166' => ['selection' => [
            'default' => '2',
            'selection' => ['2', '3']
        ]]
    ],

    'image'         => [
        'jpeg',
        'gif',
        'png',
        'tiff',
        'webp',
        'ief',
        'svg+xml'
    ],

    'password'      => [
        'nist', 'enisa'
    ],

    'coordinate'    => [
        'latitude' => [
            'default' => 'decimal',
            'selection' => ['decimal', 'dms']
        ],
        'longitude' => [
            'default' => 'dms',
            'selection' => ['decimal', 'dms']
        ]
    ],

    'currency'      => [
        'iso-4217' => [
            'default' => 'alpha',
            'selection' => ['alpha', 'numeric']
        ]
    ],

    'hash'          => [
        'md2',
        'md4',
        'md5',
        'md6',
        'sha1',
        'sha256',
        'sha512'
    ],

    'color'         => [
        'css',
        'rgb',
        'rgba',
        'hexadecimal'
    ],

    'text'          => [
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

    'amount'        => [
        'money',
        'percent' => ['length' => 2],
        'rate' => ['length' => 2]
    ],

    'number'        => [
        'boolean' => ['length' => 1],
        'natural' => ['length' => 1],
        'integer' => [
            'default' => 'decimal',
            'selection' => ['decimal', 'hexadecimal', 'octal'],
            'length' => 1
        ],
        'real' => ['length' => 2]
    ],

    'uri'           => [
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
    ],

    'date'          => [
        'plain',
        'time',
        'year',
        'month',
        'weekday',
        'yearweek',
        'yearday'
    ],

    'time'          => [
        'plain'
    ],

    'email'         => [
        ''
    ],

    'file'          => [
        'javascript',
        'pdf',
        'sql',
        'zip'
    ],

    'binary'        => [
        ''
    ],

    'phone'         => [
        'iso-29172'
    ],

    'array'         => [
        ''
    ]
];

$context->httpResponse()
    ->body($usage)
    ->send();
