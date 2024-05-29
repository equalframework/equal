<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\orm\UsageFactory;

class DataAdapterProviderSqlMySql implements AdapterProvider {
    const CONFIG = [
        // keys match the name of the supported UsageTypes
        'number' => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'boolean'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlBoolean',
            'natural'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'integer'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'real'          => 'equal\data\adapt\adapters\sql\DataAdapterSqlReal'
        ],
        'amount' => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlReal'
        ],
        'text'      => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlText'
        ],
        'time' => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlTime'
        ],
        'datetime' => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlDateTime'
        ],
        'date' => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlDate',
            'plain'         => 'equal\data\adapt\adapters\sql\DataAdapterSqlDate',
            'datetime'      => 'equal\data\adapt\adapters\sql\DataAdapterSqlDateTime',
            'time'          => 'equal\data\adapt\adapters\sql\DataAdapterSqlDateTime',
            'year'          => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger',
            'month'         => 'equal\data\adapt\adapters\sql\DataAdapterSqlInteger'
        ],
        'image'     => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlBinary'
        ],
        'binary'    => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlBinary'
        ],
        'array'     => [
            'default'       => 'equal\data\adapt\adapters\sql\DataAdapterSqlArray'
        ]
    ];

    /**
     * Provides a DataAdapter instance, according to the given content type.
     * This method supports the Content-Type syntax (type/subtype;parameter=value) along with major format aliases (JSON, SQL, TXT).
     *
     * @param   string      $content_type   The content type or the type-alias for which the DataAdapter has to be returned.
     * We try as much as possible to use standard Content-Types as defined by RFC7231 and listed by IANA (https://www.iana.org/assignments/media-types/media-types.xhtml).
     * When adaptation implies more specific distinction, we use subtype tree for distinguishing adapters.
     *
     * @return DataAdapter
     *
     * @example
     *  application/json
     *  application/sql
     *  application/xml
     *  text/plain
     *  application/sql.t-sql
     *
     */
    public function get(string $content_type) {
        /** @var \equal\orm\usages\Usage    */
        $usage = UsageFactory::create($content_type);
        $type = $usage->getType();
        $subtype = $usage->getSubtype();
        // default adapter (identity - no conversion)
        $adapter = 'equal\data\adapt\adapters\DataAdapterDefault';
        if(isset(self::CONFIG[$type])) {
            if(isset(self::CONFIG[$type][$subtype])) {
                $adapter = self::CONFIG[$type][$subtype];
            }
            elseif(isset(self::CONFIG[$type]['default'])) {
                $adapter = self::CONFIG[$type]['default'];
            }
            else {
                // #todo - issue a log entry (missing default)
            }
        }
        else {
            // #todo - issue a log entry
        }
        return new $adapter;
    }
}
