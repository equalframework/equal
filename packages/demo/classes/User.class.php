<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace demo;

class User extends \core\User {

    public static function getColumns() {
        return [
            'new_field' => [
                'type'      => 'string',
                'multilang' => true
            ],
            'myfile' => [
                'type'      => 'file'
            ]
      ];
  }
}