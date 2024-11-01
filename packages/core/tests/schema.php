<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use equal\http\HttpRequest;
use core\User;
use core\Group;

['context' => $context, 'orm' => $orm, 'auth' => $auth, 'access' => $access] = eQual::inject(['context', 'orm', 'auth', 'access']);

$tests = [

    '1101' => [
            'description'   =>  "Request a non-existing entity that matches an existing parent",
            'act'           =>  function () use($orm) {
                    $existing = false;
                    $model = $orm->getModel('demo\core\User');
                    if($model) {
                        $existing = true;
                    }
                    return $existing;
                },
            'assert'        =>  function($existing) {
                    return $existing;
                }
        ],

    '1102' => [
            'description'   =>  "Request a non-existing entity that does not match any existing parent",
            'act'           =>  function () use ($orm){
                    $non_existing = false;
                    $model = $orm->getModel('demo\test\User');

                    if(!$model) {
                        $non_existing = true;
                    }

                    return $non_existing;
                },
            'assert'        =>  function($non_existing) {
                    return $non_existing;
                }
        ],

    '1103' => [
            'description'   =>  "Request a non-existing entity that matches an existing parent",
            'act'           =>  function () use($orm) {
                    $list = 'demo\core\User'::search()->get(true);
                    $model = $orm->getModel('demo\core\User');
                    return $list;
                },
            'assert'        =>  function($list) {
                    return (count($list) > 0);
                }
        ],
    '1104' => [
            'description'   =>  "Request a non-existing entity that matches an existing parent",
            'act'           =>  function () use($orm) {
                    $model = $orm->getModel('demo\core\User');
                    $f = $model->getField('groups_ids');
                    $descriptor = $f->getDescriptor();
                    return $descriptor;
                },
            'assert'        =>  function($descriptor) {
                    return $descriptor['foreign_object'] == 'demo\core\Group';
                }
        ],
];