<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2023
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

/*
    This script is meant to provide a list of possible values or completion, given a CLI call context for the ./equal command.
    A single parameter is expected as $argv[1], holding command line fields separated with spaces.
    Call sample:  php autocomplete.php 'equal --get = core_model_c'
*/
define('QN_BASEDIR', realpath(dirname(__FILE__)));

$values = explode(' ', $argv[1]);

if(strlen($values[1]) == 0) {
    echo '--'."\n";
    exit();
}

if(count($values) == 2) {
    if(in_array($values[1], ['', '-'])) {
        echo "--";
    }
    elseif($values[1] == '--') {
        echo "do\nget\nshow\n";
    }
    elseif(in_array($values[1], ['--d', '--do'])) {
        echo "--do=\n";
    }
    elseif(in_array($values[1], ['--g', '--ge', '--get'])) {
        echo "--get=\n";
    }
    elseif(in_array($values[1], ['--s', '--sh', '--sho', '--show'])) {
        echo "--show=\n";
    }
    exit();
}

/*
    [0] => ./equal
    [1] => --get
    [2] => =
*/
if(count($values) == 3) {
    $results = choices_level($values[1], ['']);
    $count_result = count($results);
    foreach($results as $result) {
        if($count_result > 1) {
            echo trim($result, '_')."\n";
        }
        else {
            echo $result."\n";
        }
    }
    exit();
}

/*
    [0] => ./equal
    [1] => --get
    [2] => =
    [3] => co, core_
*/
if(count($values) == 4) {
    $output = [];
    $parts = explode('_', $values[3]);
    $count_parts = count($parts);

    $results = choices_level($values[1], $parts);

    // filter results
    $map_results = [];
    foreach($results as $i => $result) {
        $val = trim($result, ' _');
        if(strlen($val) <= 0 || isset($map_results[$val])) {
            unset($results[$i]);
        }
        $map_results[$val] = true;
    }

    $count_results = count($results);

    if($count_results > 1) {
        foreach($results as $result) {
            $output[] = rtrim($result, '_');
        }
        // display number of results
        // #memo - this is a workaround for COMPREPLY not considering entries when count is lesser than 3 (observed under WIN env)
        $output[] = "({$count_results})\n";
    }
    elseif($count_results > 0) {
        if($count_parts > 1) {
            array_pop($parts);
            $output[] = implode('_', $parts).'_'.$results[0];
        }
        else {
            $output[] = $results[0];
        }
    }

    echo implode("\n", $output);

    exit();
}

/*
    [0] => ./equal
    [1] => --get
    [2] => =
    [3] => core_model_collect
    [4] => --, --fields
*/
if(count($values) == 5) {
    $results = [];
    if(in_array($values[4], ['', '-'])) {
        echo '--'."\n";
        exit();
    }

    $params = [];
    $announcement = get_announcement(trim($values[1], '-'), $values[3]);
    if(isset($announcement['params'])) {
        $params = array_keys($announcement['params']);
    }

    $clue = trim($values[4], '-');
    foreach($params as $param) {
        if(!strlen($clue) || strpos($param, $clue) === 0) {
            $results[] = $param;
        }
    }

    // filter results
    $map_results = [];
    foreach($results as $i => $result) {
        $val = trim($result);
        if(strlen($val) <= 0 || isset($map_results[$val])) {
            unset($results[$i]);
        }
        $map_results[$val] = true;
    }

    $count_results = count($results);

    if($count_results > 0) {
        if($count_results == 1) {
            if($values[4] == '--'.$results[0]) {
                echo '--'.$results[0].'='."\n";
            }
            else {
                echo '--'.$results[0]."\n";
            }
        }
        else {
            foreach($results as $result) {
                echo $result."\n";
            }
        }
    }

    exit();
}

/*
    [0] => ./equal
    [1] => --get
    [2] => =
    [3] => core_model_collect
    [4] => --entity
    [5] => =
    ( [6] => aa )
*/
if(count($values) == 6 || count($values) == 7) {
    $param = trim($values[4], '-');
    $clue = '';

    if(count($values) == 7) {
        $clue = trim($values[6], "'");
    }

    $params = [];
    $announcement = get_announcement(trim($values[1], '-'), $values[3]);
    if(isset($announcement['params'])) {
        $params = $announcement['params'];
    }
    if(isset($params[$param])) {
        $usage = (isset($params[$param]['usage']))?$params[$param]['usage']:'';

        if($usage == 'orm/entity') {
            $entities = get_entities();
            foreach($entities as $entity) {
                if(!strlen($clue) || strpos($entity, $clue) === 0) {
                    echo $entity."\n";
                }
            }
        }
    }
    exit();
}


/**
 * #memo - Utilities below are hoisted when script is parsed.
 */

function get_entities() {
    $entities = [];
    $command = 'php '.QN_BASEDIR.'/run.php --get=config_classes';

    $output = null;
    if(exec($command, $output) !== false) {
        $result = json_decode(implode("\n", $output), true);
        foreach($result as $package => $classes) {
            foreach($classes as $class) {
                $entities[] = "$package\\$class";
            }
        }
    }
    return $entities;
}

function get_announcement($operation, $controller) {
    $announcement = [];
    $command = 'php '.QN_BASEDIR.'/run.php --'.$operation.'='.$controller.' --announce';

    $output = null;
    if(exec($command, $output) !== false) {
        $announce = json_decode(implode("\n", $output), true);
        if(isset($announce['announcement'])) {
            $announcement = $announce['announcement'];
        }
    }
    return $announcement;
}

function choices_packages() {
    $choices = [];
    foreach(glob(QN_BASEDIR.'/packages/*', GLOB_ONLYDIR) as $directory) {
        $choices[] = basename($directory);
    }
    return $choices;
}

function choices_root($operation) {
    $choices = choices_level($operation, ['core', '']);
    foreach(choices_packages() as $choice) {
        $choices[] = $choice.'_';
    }
    return $choices;
}

function choices_level(string $operation, array $parts) {
    $choices = [];
    $map_folders = [
            '--do'      => 'actions',
            '--get'     => 'data',
            '--show'    => 'apps'
        ];

    $count_parts = count($parts);
    // clue is always the last item, and can be empty
    $clue = $parts[$count_parts-1];

    if(in_array($operation, array_keys($map_folders))) {
        // clue only
        if($count_parts == 1) {
            foreach(choices_root($operation) as $choice) {
                // filter results
                if(!strlen($clue) || strpos($choice, $clue) === 0) {
                    $choices[] = $choice;
                }
            }
        }
        // several parts
        else {
            $packages = choices_packages();

            if(!in_array($parts[0], $packages)) {
                array_unshift($parts, 'core');
            }

            $folder = $map_folders[$operation];

            $package = array_shift($parts);

            $path_test = QN_BASEDIR.'/packages/'.$package.'/'.$folder.'/'.implode('/', $parts);

            if(!is_dir($path_test)) {
                // last part is a clue, ignore it
                array_pop($parts);
                $path_test = dirname($path_test);
                if(!is_dir($path_test)) {
                    // invalid path
                    return [];
                }
            }

            // suggest all files and dirs within that folder
            foreach(glob($path_test.'/*') as $node) {
                $choice = '';
                if(is_dir($node)) {
                    $choice = basename($node).'_';
                }
                elseif(strpos($node, '.php') > 0) {
                    $choice = basename($node, '.php');
                }
                // filter results
                if(!strlen($clue) || strpos($choice, $clue) === 0) {
                    $choices[] = $choice;
                }
            }
        }
    }

    return $choices;
}