<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

/**
 * A global var `$test` is expected to be set by each tests set (that var is used in the `core_package_test` controller).
 * As the current file is injected in the global scope, this line is not mandatory and is left in order to ease the understanding.
 *
 * @var array   $test   Global var holding the test descriptors.
 */

use equal\data\adapt\{
    DataAdapterJson,
    DataAdapterSql,
    DataAdapterTxt,
    DataAdapterProviderJson,
    DataAdapterProviderSql,
    DataAdapterProviderTxt
};
use equal\data\adapt\adapters\json\{
    DataAdapterJsonBoolean,
    DataAdapterJsonInteger,
    DataAdapterJsonReal
};
use equal\orm\UsageFactory;

global $test;

$tests = [

    // Usage Factory tests
    '1101' => [
            'description'   =>  "Usage Factory: identities assertions - numeric/real",
            'act'           =>  function () {
                    /** @var \equal\orm\usages\Usage    */
                    $usage = UsageFactory::create('numeric/real');
                    return $usage;
                },
            'assert'        =>  function($usage) {
                    return ($usage->getName() == 'numeric/real');
                }
        ],
    '1102' => [
            'description'   =>  "Usage Factory: identities assertions - numeric/integer",
            'act'           =>  function () {
                    /** @var \equal\orm\usages\Usage    */
                    $usage = UsageFactory::create('numeric/integer');
                    return $usage;
                },
            'assert'        =>  function($usage) {
                    return ($usage->getName() == 'numeric/integer');
                }
        ],
    '1103' => [
            'description'   =>  "Usage Factory: identities assertions - numeric/boolean",
            'act'           =>  function () {
                    /** @var \equal\orm\usages\Usage    */
                    $usage = UsageFactory::create('numeric/boolean');
                    return $usage;
                },
            'assert'        =>  function($usage) {
                    return ($usage->getName() == 'numeric/boolean');
                }
        ],
    '1104' => [
            'description'   =>  "Usage Factory: identities assertions - numeric/natural",
            'act'           =>  function () {
                    /** @var \equal\orm\usages\Usage    */
                    $usage = UsageFactory::create('numeric/natural');
                    return $usage;
                },
            'assert'        =>  function($usage) {
                    return ($usage->getName() == 'numeric/natural');
                }
        ],

    '1201' => [
            'description'   =>  "Usage Factory: identities assertions - amount/money",
            'act'           =>  function () {
                    /** @var \equal\orm\usages\Usage    */
                    $usage = UsageFactory::create('amount/money');
                    return $usage;
                },
            'assert'        =>  function($usage) {
                    return ($usage->getName() == 'amount/money');
                }
        ],


    // providers tests
    '2101' => [
            'description'   =>  "DataAdapterProvider: provider retrieval - JSON",
            'act'           =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterJson);
                }
        ],

    '2201' => [
            'description'   =>  "DataAdapterProvider: provider retrieval - SQL",
            'act'           =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('sql');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterSql);
                }
        ],
    '2301' => [
            'description'   =>  "DataAdapterProvider: provider retrieval - TXT",
            'act'           =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('txt');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterTxt);
                }
        ],

    // DataAdapters retrieval
    '3101' => [
            'description'   =>  "DataAdapterProvider: DataAdatper retrieval - json/integer",
            'act'           =>  function () {
                    $dap = new DataAdapterProviderJson();
                    $adapter = $dap->get('number/integer:10{0,100}');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterJsonInteger);
                }
        ],
    '3102' => [
            'description'   =>  "DataAdapterProvider: DataAdatper retrieval - json/boolean",
            'act'           =>  function () {
                    $dap = new DataAdapterProviderJson();
                    $adapter = $dap->get('number/boolean');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterJsonBoolean);
                }
        ],
    '3103' => [
            'description'   =>  "DataAdapterProvider: DataAdatper retrieval - json/real",
            'act'           =>  function () {
                    $dap = new DataAdapterProviderJson();
                    $adapter = $dap->get('number/real:10.5');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterJsonReal);
                }
        ],

    // Adaptation tests
    '4001' => [
            'description'   =>  "JSON adapter IN - number/real",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn(1.5, 'number/real');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 1.5);
                }
        ],
    '4002' => [
            'description'   =>  "JSON adapter IN - number/boolean",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('true', 'number/boolean');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '4003' => [
            'description'   =>  "JSON adapter IN - number/natural",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('2', 'number/natural');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === 2);
                }
        ],
    '4004' => [
            'description'   =>  "JSON adapter IN - number/integer",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('-2', 'number/integer');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === -2);
                }
        ],
    '4101' => [
            'description'   =>  "JSON adapter OUT - number/real",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1.5, 'number/real');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 1.5);
                }
        ],
    '4102' => [
            'description'   =>  "JSON adapter OUT - number/boolean",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1, 'number/boolean');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '4103' => [
            'description'   =>  "JSON adapter OUT - number/natural",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(2, 'number/natural');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === 2);
                }
        ],
    '4104' => [
            'description'   =>  "JSON adapter OUT - number/integer",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(-2, 'number/integer');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === -2);
                }
        ]

];
