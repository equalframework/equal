<?php
namespace proposal;


class Task extends \qinoa\orm\Model {
    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'alias',
                'alias'             => 'label',
            ],
            'label' => [
                'type'              => 'string',
            ],
            'description' => [
                'type'              => 'string',
                'usage'              => 'string',                
            ],
            'duration' => [
                'type'              => 'float',
            ],
            'order' => [
                'type'              => 'integer',
            ],
            'price' => [
                'type'              => 'integer',
            ],
            'stage_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'proposal\Stage',
            ],
        ];
	}
}