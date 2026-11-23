<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageSecret extends Usage {

    private const DEFAULT_LENGTH = 65000;

    private const SUBTYPES = [
        '',
        'password',
        'token',
        'api-key',
        'private-key'
    ];

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);

        if(!in_array($this->getSubtype(), self::SUBTYPES, true)) {
            trigger_error('ORM::unsupported secret usage subtype.', EQ_REPORT_WARNING);
            throw new \Exception('invalid_secret_usage', EQ_ERROR_INVALID_PARAM);
        }

        if($this->length === 0) {
            $this->length = self::DEFAULT_LENGTH;
        }
    }

    public function getConstraints(): array {
        return [
            'invalid_secret_type' => [
                'message'  => 'Secret value must be a string.',
                'function' => function($value) {
                    return is_string($value);
                }
            ],
            'size_exceeded' => [
                'message'  => 'String exceeds usage length constraint.',
                'function' => function($value) {
                    return !is_string($value) || strlen($value) <= $this->getLength();
                }
            ]
        ];
    }

}
