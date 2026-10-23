<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

$view_file = EQ_BASEDIR.'/packages/core/views/Group.form.inheritance-test.json';

$tests = [
    '0101' => [
        'description' => 'Child metadata overrides inherited view metadata',
        'arrange'     => function() use($view_file) {
            $view = [
                'name'        => 'Child view',
                'description' => 'Child view description.',
                'extends'     => [
                    'entity' => 'core\\User',
                    'view'   => 'form.default'
                ],
                'layout'      => [
                    'update' => []
                ]
            ];

            file_put_contents(
                $view_file,
                json_encode($view, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL
            );

            return $view_file;
        },
        'act'         => function() {
            return eQual::run('get', 'model_view', [
                'entity'  => 'core\\Group',
                'view_id' => 'form.inheritance-test'
            ]);
        },
        'assert'      => function($view) {
            return ($view['name'] ?? null) === 'Child view'
                && ($view['description'] ?? null) === 'Child view description.';
        },
        'rollback'    => function() use($view_file) {
            if(file_exists($view_file)) {
                unlink($view_file);
            }
        }
    ]
];
