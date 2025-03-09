<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the list of UML files defined in a given package.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'params'        => [
        'type'      => [
            'description'   => 'Type of the UML data',
            'type'          => 'string',
            'selection'     => ['erd']
        ],
        'package'   => [
            'description'   => 'The specific package to retrieve UML files from',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

$package = $params['package'];
$umlPath = QN_BASEDIR . "/packages/{$package}/uml";

$result = recurse_dir($umlPath, "json", $params['type']);

$context->httpResponse()
        ->body(json_encode([$package => $result]))
        ->send();

function has_sub_items($directory, $extension) {
    $files = glob($directory . '/*.' . $extension);
    if (count($files)) {
        return true;
    }
    foreach (glob($directory . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $node) {
        if (has_sub_items($node, $extension)) {
            return true;
        }
    }
    return false;
}

function endsWith($haystack, $needle) {
    $length = strlen($needle);
    return !$length || substr($haystack, -$length) === $needle;
}

/**
 * #memo - this method highly differs from the one in controllers.php , translations.php and menu.php
*/
function recurse_dir($directory, $extension, $type, $parent_name = '') {
    $result = [];
    if (is_dir($directory)) {
        $list = glob($directory . '/*');
        foreach ($list as $node) {
            $filename = basename($node, '.' . $extension);
            if (is_dir($node)) {
                if (!has_sub_items($node, $extension)) {
                    continue;
                }
                $result = array_merge($result, recurse_dir($node, $extension, $type, $parent_name . '/' . $filename));
            } elseif (pathinfo($node, PATHINFO_EXTENSION) == $extension && endsWith($filename, $type)) {
                $entity = (strlen($parent_name) ? $parent_name . '/' : '') . $filename . '.' . $extension;
                $result[] = $entity;
            }
        }
    }
    return $result;
}

