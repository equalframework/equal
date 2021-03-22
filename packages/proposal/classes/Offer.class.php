<?php
namespace proposal;


class Offer extends \qinoa\orm\Model {
    public static function getColumns() {
        return [
          'name' => [
            'type' => 'alias',
            'alias' => 'title'
          ],
          'title' => [
            'type' => 'string'
          ],
          'description' => [
            'type' => 'string'
          ]
        ];
	}
}