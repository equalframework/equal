<?php
namespace test;


class User extends \core\User {
    public static function getColumns() {
        return array (
  'new_field' => 
  array (
    'type' => 'html',
    'multilang' => NULL,
  ),
  'myfile' => 
  array (
    'type' => 'file',
  ),
);
	}
}
