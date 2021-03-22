<?php
namespace proposal;


class Customer extends \qinoa\orm\Model {
    public static function getColumns() {
        return array (
  'name' => 
  array (
    'type' => 'string',
  ),
  'oranisation_name' => 
  array (
    'type' => 'string',
  ),
  'organisation_VAT' => 
  array (
    'type' => 'string',
  ),
  'address_street' => 
  array (
    'type' => 'string',
  ),
  'address_country' => 
  array (
    'type' => 'string',
  ),
  'address_city' => 
  array (
    'type' => 'string',
  ),
  'address_zip' => 
  array (
    'type' => 'string',
  ),
  'contact_firstname' => 
  array (
    'type' => 'string',
  ),
  'contact_lastname' => 
  array (
    'type' => 'string',
  ),
  'contact_gender' => 
  array (
    'type' => 'string',
  ),
  'contact_birthdate' => 
  array (
    'type' => 'date',
  ),
);
	}
}
