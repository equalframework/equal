<?php
namespace lodging\sale\product;


class ProductModel extends \symbiose\sale\product\ProductModel {

	public static function getName() {
        return "Product Model";
    }

    public static function getColumns() {

        return [
            'qty_accounting_method' => [
                'type'              => 'string',
                'description'       => 'The way the product quantity has to be computed (per unit [default], per person, or per accomodation [resource]).',
                'selection'         => ['person', 'accomodation', 'unit']
            ],

            'rental_unit_assignement' => [
                'type'              => 'string',
                'description'       => 'The way the product is assigned to a rental unit (a specific unit, or a category).',
                'selection'         => ['unit', 'category'],
                'visible'           => [ ['qty_accounting_method', '=', 'accomodation'] ]
            ],

            'duration' => [
                'type'              => 'integer',
                'description'       => 'Additional information about the duration of the service (used for planning purpose).',
                'visible'           => [ ['qty_accounting_method', '=', 'person'] ]
            ],

            'capacity' => [
                'type'              => 'integer',
                'description'       => 'Additional information about the capacity implied by the service (used for finding matching rental units).',
                'visible'           => [ ['qty_accounting_method', '=', 'accomodation'] ]
            ],

            // a product either refers to a specific rental unit, or to a category of rental units (both allowing to find matching units a given period and a capacity)
            'rental_unit_category_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'equal\sale\product\Family',
                'description'       => "Rental Unit Category this Product related to, if any.",
                'visible'           => [ ['qty_accounting_method', '=', 'accomodation'], ['rental_unit_assignement', '=', 'category'] ]
            ],

            'rental_unit_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'equal\sale\product\Family',
                'description'       => "Specific Rental Unit this Product related to, if any",
                'visible'           => [ ['qty_accounting_method', '=', 'accomodation'], ['rental_unit_assignement', '=', 'unit'] ]
            ],

            'stat_rule_id' => [
            ]


        ];
    }


    public static function getDefaults() {
        return [
            'qty_accounting_method'    => function() { return 'unit'; }
        ];
    }    
}