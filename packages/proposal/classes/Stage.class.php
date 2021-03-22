<?php
namespace proposal;


class Stage extends \qinoa\orm\Model {
    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'label'
            ],        
            'label' => [
                'type'              => 'string',
            ],
            'order' => [
                'type'              => 'integer',
            ],
            'parent_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'proposal\Stage',
            ],
            
        ];
	}
}
