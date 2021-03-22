<?php
namespace proposal;


class OfferLine extends \qinoa\orm\Model {
    public static function getColumns() {
        return [
            'description' => [
                'type'              => 'string',
            ],
            'quantity' => [
                'type'              => 'float',
            ],
            'unit_price' => [
                'type'              => 'float',
            ],
            'task_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'proposal\Task',
            ],            
        ];
	}
}
