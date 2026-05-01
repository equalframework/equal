<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Provide a unique identifier of the current git revision.\n" .
                       "This script assumes the current installation is versioned using git\n" .
                       "and that a `.git` folder is present at the root of the installation.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'params'    => [],
    'providers' => ['context']
]);

$context = $providers['context'];

try {
    if(!is_dir(EQ_BASEDIR . '/.git')) {
        throw new Exception('git repository not found', EQ_ERROR_INVALID_CONFIG);
    }

    // get short commit hash
    $commit = trim(shell_exec('git -C ' . escapeshellarg(EQ_BASEDIR) . ' rev-parse --short HEAD'));

    if(!$commit) {
        throw new Exception('unable to retrieve git commit', EQ_ERROR_INVALID_CONFIG);
    }

    // get commit date (more reliable than index mtime)
    $date = trim(shell_exec(
        'git -C ' . escapeshellarg(EQ_BASEDIR) . ' log -1 --format=%cd --date=format:%Y.%m.%d'
    ));

    if(!$date) {
        throw new Exception('unable to retrieve git date', EQ_ERROR_INVALID_CONFIG);
    }

    $revision = "$date.$commit";

    $branch = trim(shell_exec(
        'git -C ' . escapeshellarg(EQ_BASEDIR) . ' rev-parse --abbrev-ref HEAD'
    ));

    $dirty = trim(shell_exec(
        'git -C ' . escapeshellarg(EQ_BASEDIR) . ' status --porcelain'
    )) !== '';
}
catch(Exception $e) {
    trigger_error("PHP::Unable to retrieve git data: " . $e->getMessage(), EQ_REPORT_INFO);
    throw new Exception('git_data_missing', $e->getCode());
}

$context
    ->httpResponse()
    ->body([
        'revision'  => $revision,
        'commit'    => $commit,
        'branch'    => $branch,
        'dirty'     => $dirty
    ])
    ->send();