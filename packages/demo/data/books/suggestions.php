<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Suggests books for bedtime stories based on child age and interests',
    'params'        => [
        'keywords' => [
            'type'          => 'array',
            'description'   => "Keywords that catch your child attention",
            'default'       => []
        ],
        'age' => [
            'type'          => 'integer',
            'description'   => 'Your child age',
            'min'           => 4,
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;


$store = [
    4  => ['Goldielocks and the three bears', 'Three little pigs'],
    5  => ["Charlotte's web", 'The Little Prince'],
    8  => ['Charly and the chocolate factory', 'Alice in Wonderland'],
    10 => ['Harry Potter', 'The Jungle book']
];

$result = [];

foreach($store as $age => $books) {
    if($age <= $params['age']) {
        if(count($params['keywords'])) {
            foreach($params['keywords'] as $keyword) {
                foreach($books as $book) {
                    if(stripos($book, $keyword) !== false) {
                        $result[] = $book;
                    }
                }
            }
        }
        else if($age >= $params['age']-2){
            $result = array_merge($result, $books);
        }
    }
    else break;
}

   
$context
    ->httpResponse()
    ->body($result)
    ->send();