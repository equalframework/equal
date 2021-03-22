<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Suggests books for bedtime stories based on child age and interests',
    'params'        => [
        'keywords' => [
            'description'   => "Keywords that catch your child attention",
            'type'          => 'array',
            'default'       => []
        ],
        'age' => [
            'description'   => 'Your child age',
            'type'          => 'integer',
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

list($context) = [$providers['context']];


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

   
$context->httpResponse()->body($result)->send();