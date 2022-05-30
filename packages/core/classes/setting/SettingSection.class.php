<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\setting;

use equal\orm\Model;

class SettingSection extends Model {

    public static function getName() {
        return 'Setting Section';
    }

    public static function getDescription() {
        return "Sections allow to group configurations parameters.";
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Title of the secftion.",
                'multilang'         => true
            ],

            'description' => [
                'type'              => 'string',
                'description'       => "Short description of the section (which parameters it regroups).",
                'multilang'         => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Unique code of the parameter.',
                'onupdate'          => 'onupdateCode',
                'required'          => true
            ],
        
            'settings_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\setting\Setting',
                'foreign_field'     => 'section_id',
                'description'       => 'List of settings related to the section.'
            ]

        ];
    }

    public static function onupdateCode($om, $ids, $values, $lang) {
        
        $sections = $om->read(__CLASS__, $ids, ['settings_ids'], $lang);
        if($sections > 0 && count($sections)) {
            foreach($sections as $oid => $odata) {
                $om->write('core\setting\Setting', $odata['settings_ids'], ['name' => null], $lang);
            }    
        }
        
    }

    public function getUnique() {
        return [
            ['code']
        ];
    }
}