<?php
/*
    This file is part of the eQual framework <https://github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\organic\Service;

class DataAdapterProvider extends Service implements AdapterProvider {

    const CONFIG = [
        'JSON'      => 'equal\data\adapt\DataAdapterJson',
        'SQL'       => 'equal\data\adapt\DataAdapterSql',
        'TXT'       => 'equal\data\adapt\DataAdapterTxt'
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
    public function get(string $content_type): DataAdapter {
        static $adapters = [];

        /** @var DataAdapter */
        $adapterInstance = null;

        if(!preg_match('/([a-zA-Z]+)\/?([-+.a-z0-9]+)?(;(.+))*/', $content_type, $matches)) {
            throw new \Exception('invalid_content_type', QN_ERROR_INVALID_PARAM);
        }

        $type = $matches[1];
        $subtype = (isset($matches[2]))?$matches[2]:'';

        // $params = $matches[4];
        if( ($type == 'application' && $subtype == 'json') || strcasecmp($type, 'JSON') == 0 ) {
            if(isset($adapters['JSON'])) {
                $adapterInstance = $adapters['JSON'];
            }
            elseif(isset(self::CONFIG['JSON'])) {
                $adapter = self::CONFIG['JSON'];
                $adapterInstance = new $adapter;
            }
        }
        elseif( ($type == 'application' && $subtype == 'sql') || strcasecmp($type, 'SQL') == 0 ) {
            if(isset($adapters['SQL'])) {
                $adapterInstance = $adapters['SQL'];
            }
            elseif(isset(self::CONFIG['SQL'])) {
                $adapter = self::CONFIG['SQL'];
                $adapterInstance = new $adapter;
            }
        }
        elseif($type == 'text' || strcasecmp($type, 'TXT') == 0) {
            if(isset($adapters['TXT'])) {
                $adapterInstance = $adapters['TXT'];
            }
            elseif(isset(self::CONFIG['TXT'])) {
                $adapter = self::CONFIG['TXT'];
                $adapterInstance = new $adapter;
            }
        }

        return $adapterInstance;
    }
}
