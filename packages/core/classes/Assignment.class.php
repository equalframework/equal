<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Assignment extends Model {

    public static function getDescription() {
        return 'Assignments consist of one or more roles assigned to users on specific objects.';
    }

    public static function getColumns() {
        return [
            'object_class' => [
                'type'              => 'string',
                'description'       => 'Full name of the entity on which the role assignment applies.',
                'required'          => true
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the specific object on which the role is assigned."
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => "User the role is assigned to."
            ],

            'role' => [
                'type' 	            => 'string',
                'description'       => "Role that is assigned to the user.",
                'help'              => "The assigned Role should match one of the roles defined at the entity level and returned by the `getRole()` method."
            ]
        ];
    }

    /**
     * Handler for single object values change in UI.
     * This method does not imply an actual update of the model, but a potential one (not made yet) and is intended for front-end only.
     *
     * @param  array            $event      Associative array holding changed fields as keys, and their related new values.
     * @return array            Returns an associative array mapping fields with their resulting values.
     */
    public static function onchange($event) {
        $result = [];
        if(isset($event['object_class']) && method_exists($event['object_class'], 'getRoles')) {
            $map_roles = $event['object_class']::getRoles();
            $result['role'] = [
                'selection' => array_keys($map_roles)
            ];
        }
        return $result;
    }

}
