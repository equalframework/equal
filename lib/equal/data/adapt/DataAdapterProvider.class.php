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
     * We try as much as possible to use standard Content-Types as defined by RFC7231 and listed by IANA (https://www.iana.org/assignments/media-types/media-types.xhtml)
     * application/json
     * application/sql
     * application/xml
     * text/plain
     *
     * When adaptation implies more specific distinction, we use subtype tree for distinguishing adapters :
     * application/sql.t-sql
     *
     * In any case we follow the Content-Type syntax: type/subtype;parameter=value
     */
    public function get($content_type): DataAdapter {
        /** @var DataAdapter */
        $adapter = null;
        if(!preg_match('/([a-z]+)\/([-+.a-z0-9]+)(;(.+))*/', $content_type, $matches)) {
            // error
        }
        $type = $matches[1];
        $subtype = $matches[2];
        $params = $matches[4];
        if($type == 'application') {
            if($subtype == 'json') {
                return new DataAdapterJson();
            }
            elseif($subtype == 'sql') {

            }
        }
        elseif($type == 'text') {

        }
        return $adapter;
    }
}