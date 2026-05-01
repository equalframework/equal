<?php

use equal\http\HttpRequest;

[$params, $providers] = eQual::announce([
    'description'   => "Provide the declared version of eQual with optional enrichment from git or GitHub.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'params'    => [],
    'providers' => ['context']
]);

$context = $providers['context'];

$source = 'version';
$version_file = EQ_BASEDIR . '/VERSION';

if(!file_exists($version_file)) {
    throw new Exception('version_file_missing', EQ_ERROR_INVALID_CONFIG);
}

$version = trim(file_get_contents($version_file));

if(!$version) {
    throw new Exception('version_file_empty', EQ_ERROR_INVALID_CONFIG);
}

$response = [
    'version' => $version
];


// local Git info
if(is_dir(EQ_BASEDIR . '/.git')) {
    try {
        $branch = trim(shell_exec(
            'git -C ' . escapeshellarg(EQ_BASEDIR) . ' rev-parse --abbrev-ref HEAD'
        ));

        if($branch) {
            $response['branch'] = $branch;
        }

        $commit = trim(shell_exec(
            'git -C ' . escapeshellarg(EQ_BASEDIR) . ' rev-parse --short HEAD'
        ));

        if($commit) {
            $response['commit'] = $commit;
        }

        $date = trim(shell_exec(
            'git -C ' . escapeshellarg(EQ_BASEDIR) . ' log -1 --format=%cd --date=format:%Y.%m.%d'
        ));

        if($date) {
            $response['date'] = $date;
        }

        $response['dirty'] = trim(shell_exec(
            'git -C ' . escapeshellarg(EQ_BASEDIR) . ' status --porcelain'
        )) !== '';

        $source = 'git';
    }
    catch(Exception $e) {
        trigger_error("PHP::Git detection failed: " . $e->getMessage(), EQ_REPORT_INFO);
    }
}
// GitHub fallback
elseif(preg_match('/^[0-9]+\.[0-9]+/', $version)) {

    try {
        $tag = 'v' . $version;

        // appel direct (plus simple que ref + commit)
        $request = new HttpRequest("https://api.github.com/repos/equalframework/equal/commits/$tag");

        $httpResponse = $request->send();

        $data = $httpResponse->getBody();

        if(!empty($data['sha'])) {
            $commit = substr($data['sha'], 0, 8);
            $date_iso = $data['author']['date'] ?? null;

            if($date_iso) {
                $date = date("Y.m.d", strtotime($date_iso));
                $response['date'] = $date;
            }

            $response['commit'] = $commit;
            $source = 'github';
        }
    }
    catch(Exception $e) {
        trigger_error("PHP::GitHub lookup failed: " . $e->getMessage(), EQ_REPORT_INFO);
    }
}


// version / branch alignment
if(isset($response['branch'])) {
    $version_mismatch = true;

    $branch = $response['branch'];

    if($branch === 'master' || $branch === 'main') {
        $version_mismatch = false;
    }
    elseif($branch === $version) {
        $version_mismatch = false;
    }
    $response['mismatch'] = $version_mismatch;
}


$response['source'] = $source;


$context
    ->httpResponse()
    ->body($response)
    ->send();