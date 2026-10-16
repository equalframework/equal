<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\UsageFactory;

class DataAdapterProviderSqlPostgreSql implements AdapterProvider {
    const CONFIG = [
        'number' => [
            'default' => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'boolean' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlBooleanPostgreSql',
            'natural' => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'integer' => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'real'    => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlRealPostgreSql'
        ],
        'amount' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlRealPostgreSql'
        ],
        'text' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlTextPostgreSql'
        ],
        'phone' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlTextPostgreSql'
        ],
        'email' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlTextPostgreSql'
        ],
        'password' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlTextPostgreSql'
        ],
        'time' => [
            'default' => 'equal\data\adapt\adapters\sql\DataAdapterSqlTime'
        ],
        'datetime' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlDateTimePostgreSql'
        ],
        'date' => [
            'default'  => 'equal\data\adapt\adapters\sql\DataAdapterSqlDate',
            'plain'    => 'equal\data\adapt\adapters\sql\DataAdapterSqlDate',
            'datetime' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlDateTimePostgreSql',
            'time'     => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlDateTimePostgreSql',
            'year'     => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'month'    => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger'
        ],
        'image' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlBinaryPostgreSql'
        ],
        'binary' => [
            'default' => 'equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlBinaryPostgreSql'
        ],
        'array' => [
            'default' => 'equal\data\adapt\adapters\sql\DataAdapterSqlArray'
        ]
    ];

    public function get(string $content_type) {
        $usage = UsageFactory::create($content_type);
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        $adapter = 'equal\data\adapt\adapters\DataAdapterDefault';

        if(isset(self::CONFIG[$type][$subtype])) {
            $adapter = self::CONFIG[$type][$subtype];
        }
        elseif(isset(self::CONFIG[$type]['default'])) {
            $adapter = self::CONFIG[$type]['default'];
        }

        return new $adapter;
    }
}
