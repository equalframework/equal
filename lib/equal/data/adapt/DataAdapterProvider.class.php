<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\data\adapt;

use equal\organic\Service;

class DataAdapterProvider extends Service {

    /**
     * Provides a DataAdapter object according to given type.
     * This method supports the Content-Type syntax (type/subtype;parameter=value) along with major types aliases (JSON, SQL, TXT)
     *
     * @param   string      $content_type   The content type or the type-alias for which the DataAdapter has to be returned.
     * We try as much as possible to use standard Content-Types as defined by RFC7231 and listed by IANA (https://www.iana.org/assignments/media-types/media-types.xhtml).
     * When adaptation implies more specific distinction, we use subtype tree for distinguishing adapters.
     *
     * @example
     *  application/json
     *  application/sql
     *  application/xml
     *  text/plain
     *  application/sql.t-sql
     *
     */
    public function get($content_type): DataAdapter {
        /** @var DataAdapter */
        $adapter = null;
        if(!preg_match('/([a-zA-Z]+)\/?([-+.a-z0-9]+)?(;(.+))*/', $content_type, $matches)) {
            throw new \Exception('invalid_content_type', QN_ERROR_INVALID_PARAM);
        }
        $type = $matches[1];
        $subtype = (isset($matches[2]))?$matches[2]:'';
        // $params = $matches[4];
        if( ($type == 'application' && $subtype == 'json') || strcasecmp($type, 'JSON') == 0 ) {
            return new DataAdapterJson();
        }
        else if( ($type == 'application' && $subtype == 'sql') || strcasecmp($type, 'SQL') == 0 ) {
            return new DataAdapterSql();
        }
        elseif($type == 'text' || strcasecmp($type, 'TXT') == 0) {
            return new DataAdapterTxt();
        }
        return $adapter;
    }
}
