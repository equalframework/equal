<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\services\Container;

(static function(): void {
    $db = Container::getInstance()->get('db')->connect();

    if(!$db) {
        throw new Exception('missing_database', EQ_ERROR_INVALID_CONFIG);
    }

    $indexes = [
        ['object_class', 'object_id', 'object_field', 'language'],
        ['object_class', 'language', 'object_id', 'object_field']
    ];
    $existing_indexes = [];

    if(in_array(constant('DB_DBMS'), ['MYSQL', 'MARIADB'], true)) {
        $result = $db->sendQuery(
            'SELECT MAX(CHAR_LENGTH(`object_class`)) AS `object_class_length`, '
            .'MAX(CHAR_LENGTH(`object_field`)) AS `object_field_length` '
            .'FROM `core_translation`;'
        );
        $lengths = $db->fetchArray($result);

        if(
            (int) ($lengths['object_class_length'] ?? 0) > 128
            || (int) ($lengths['object_field_length'] ?? 0) > 64
        ) {
            trigger_error(
                'Update cannot reduce core_translation columns without truncating existing values.',
                EQ_REPORT_ERROR
            );
            throw new Exception('translation_index_value_too_long', EQ_ERROR_INVALID_CONFIG);
        }

        $db->sendQuery(
            'ALTER TABLE `core_translation` '
            .'MODIFY `object_class` VARCHAR(128) DEFAULT NULL, '
            .'MODIFY `object_field` VARCHAR(64) DEFAULT NULL;'
        );

        $result = $db->sendQuery(
            'SELECT DISTINCT `INDEX_NAME` FROM `INFORMATION_SCHEMA`.`STATISTICS` '
            .'WHERE `TABLE_SCHEMA` = DATABASE() AND `TABLE_NAME` = \'core_translation\';'
        );

        while($index = $db->fetchArray($result)) {
            $existing_indexes[$index['INDEX_NAME']] = true;
        }
    }

    foreach($indexes as $index_fields) {
        $index_name = $db->getCompositeIndexName($index_fields);

        if(isset($existing_indexes[$index_name])) {
            continue;
        }

        $query = $db->getQueryAddCompositeIndex('core_translation', $index_fields);

        if(is_string($query) && strlen($query)) {
            $db->sendQuery($query);
        }
    }
})();
