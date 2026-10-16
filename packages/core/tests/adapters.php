<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
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
    DataAdapterProviderSqlMySql,
    DataAdapterProviderSqlPostgreSql,
    DataAdapterProviderSqlSqlite,
    DataAdapterProviderSqlSqlSrv,
    DataAdapterProviderTxt
};
use equal\data\adapt\adapters\json\{
    DataAdapterJsonBoolean,
    DataAdapterJsonInteger,
    DataAdapterJsonReal,
    DataAdapterJsonText
};
use equal\data\adapt\adapters\sql\DataAdapterSqlText;
use equal\data\adapt\adapters\sql\mysql\DataAdapterSqlTextMySql;
use equal\data\adapt\adapters\sql\postgresql\DataAdapterSqlTextPostgreSql;
use equal\data\adapt\adapters\sql\sqlsrv\DataAdapterSqlTextSqlSrv;
use equal\data\adapt\adapters\txt\DataAdapterTxtText;
use equal\data\DataFormatter;
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
    '3104' => [
            'description'   =>  "DataAdapterProvider: DataAdatper retrieval - sql/password",
            'act'           =>  function () {
                    $dap = new DataAdapterProviderSql();
                    $adapter = $dap->get('password');
                    return $adapter;
                },
            'assert'        =>  function($adapter) {
                    return ($adapter instanceof DataAdapterSqlText);
                }
        ],
    '3105' => [
            'description'   =>  "DataAdapterProvider: text adapters retrieval - phone",
            'act'           =>  function () {
                    $providers = [
                        [new DataAdapterProviderJson(), DataAdapterJsonText::class],
                        [new DataAdapterProviderTxt(), DataAdapterTxtText::class],
                        [new DataAdapterProviderSqlMySql(), DataAdapterSqlTextMySql::class],
                        [new DataAdapterProviderSqlPostgreSql(), DataAdapterSqlTextPostgreSql::class],
                        [new DataAdapterProviderSqlSqlite(), DataAdapterSqlText::class],
                        [new DataAdapterProviderSqlSqlSrv(), DataAdapterSqlTextSqlSrv::class]
                    ];
                    foreach($providers as [$provider, $expectedClass]) {
                        if(!($provider->get('phone') instanceof $expectedClass)) {
                            return false;
                        }
                    }
                    return true;
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '3106' => [
            'description'   =>  "DataAdapterProvider: text adapters retrieval - email",
            'act'           =>  function () {
                    $providers = [
                        [new DataAdapterProviderJson(), DataAdapterJsonText::class],
                        [new DataAdapterProviderTxt(), DataAdapterTxtText::class],
                        [new DataAdapterProviderSqlMySql(), DataAdapterSqlTextMySql::class],
                        [new DataAdapterProviderSqlPostgreSql(), DataAdapterSqlTextPostgreSql::class],
                        [new DataAdapterProviderSqlSqlite(), DataAdapterSqlText::class],
                        [new DataAdapterProviderSqlSqlSrv(), DataAdapterSqlTextSqlSrv::class]
                    ];
                    foreach($providers as [$provider, $expectedClass]) {
                        if(!($provider->get('email') instanceof $expectedClass)) {
                            return false;
                        }
                    }
                    return true;
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '3107' => [
            'description'   =>  "DataAdapterProvider: PostgreSQL-specific SQL adapters",
            'act'           =>  function () {
                    $provider = new DataAdapterProviderSqlPostgreSql();
                    $boolean = $provider->get('number/boolean');
                    $binary = $provider->get('binary');
                    $datetime = $provider->get('datetime');
                    $real = $provider->get('number/real:10.2');
                    $text = $provider->get('text/plain:128');
                    return [
                        $boolean->castOutType(),
                        $boolean->adaptIn('t', 'number/boolean'),
                        $boolean->adaptIn('f', 'number/boolean'),
                        $binary->castOutType(),
                        $binary->adaptIn('\\x4142', 'binary'),
                        $datetime->castOutType(),
                        $real->castOutType('number/real:10.2'),
                        $text->castOutType('text/plain:128')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        'BOOLEAN',
                        true,
                        false,
                        'BYTEA',
                        'AB',
                        'TIMESTAMP WITHOUT TIME ZONE',
                        'NUMERIC(12,2)',
                        'VARCHAR(128)'
                    ]);
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
    '4005' => [
            'description'   =>  "JSON adapter IN - time",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('10:00', 'time');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 36000);
                }
        ],
    '4006' => [
            'description'   =>  "JSON adapter IN - date",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('2018-12-10T13:49:51.141Z', 'date');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 1544400000);
                }
        ],
    '4007' => [
            'description'   =>  "JSON adapter IN - datetime",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('2018-12-10T13:49:51.141Z', 'date/datetime');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result == 1544449791);
                }
        ],
    '4008' => [
            'description'   =>  "JSON adapter IN - array (1)",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('[1,2,3,4]', 'array');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === [1, 2, 3, 4]);
                }
        ],
    '4009' => [
            'description'   =>  "JSON adapter IN - array (2)",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('["a b","ba","cd"]', 'array');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === ['a b', 'ba', 'cd']);
                }
        ],
    '4010' => [
            'description'   =>  "JSON adapter IN - array (3)",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('{a:test, b:test2, c:foo}', 'array');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === ['a' => 'test', 'b' => 'test2', 'c' => 'foo']);
                }
        ],
    '4011' => [
            'description'   =>  "JSON adapter IN - array (3)",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptIn('{"a":"test 1", "b":"test 2", "c":foo}', 'array');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === ['a' => 'test 1', 'b' => 'test 2', 'c' => 'foo']);
                }
        ],
    '4012' => [
            'description'   =>  "JSON adapter IN - phone normalization",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    return [
                        $adapter->adaptIn('+32 (478) 45.67.89', 'phone'),
                        $adapter->adaptIn('0032 478 45 67 89', 'phone'),
                        $adapter->adaptIn('0478/45.67.89', UsageFactory::create('phone')),
                        $adapter->adaptIn('+32 PHONE', 'phone'),
                        $adapter->adaptIn(null, 'phone')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        '+32478456789',
                        '0032478456789',
                        '0478456789',
                        '+32PHONE',
                        null
                    ]);
                }
        ],
    '4013' => [
            'description'   =>  "JSON adapter IN - email normalization",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    return [
                        $adapter->adaptIn(' User.Name+tag@Example.com ', 'email'),
                        $adapter->adaptIn(null, UsageFactory::create('email'))
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === ['User.Name+tag@Example.com', null]);
                }
        ],
    '4014' => [
            'description'   =>  "TXT adapter IN - phone normalization",
            'act'           =>  function () {
                    $adapter = new DataAdapterTxt();
                    return [
                        $adapter->adaptIn("+32\t(478) 45-67/89", 'phone'),
                        $adapter->adaptIn(null, 'phone')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === ['+32478456789', null]);
                }
        ],
    '4015' => [
            'description'   =>  "TXT adapter IN - email normalization",
            'act'           =>  function () {
                    $adapter = new DataAdapterTxt();
                    return [
                        $adapter->adaptIn(' User.Name+tag@Example.com ', UsageFactory::create('email')),
                        $adapter->adaptIn(null, 'email')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === ['User.Name+tag@Example.com', null]);
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
        ],
    '4105' => [
            'description'   =>  "JSON adapter OUT - amount/money",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1.95435, 'amount/money');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === 1.9544);
                }
        ],
    '4106' => [
            'description'   =>  "JSON adapter OUT - number/real",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1.955356, 'number/real:5');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === 1.95536);
                }
        ],

    '4107' => [
            'description'   =>  "JSON adapter OUT - time",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(36000, 'time');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === '10:00:00');
                }
        ],
    '4108' => [
            'description'   =>  "JSON adapter OUT - datetime",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1699907585, 'datetime');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === '2023-11-13T20:33:05+00:00');
                }
        ],
    '4109' => [
            'description'   =>  "JSON adapter OUT - date",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(1699907585, 'date');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === '2023-11-13T00:00:00+00:00');
                }
        ],
    '4110' => [
            'description'   =>  "JSON adapter OUT - array",
            'arrange'       =>  function () {
                    /** @var \equal\data\adapt\DataAdapterProvider $dap */
                    $providers = eQual::inject(['adapt']);
                    $dap = $providers['adapt'];
                    $adapter = $dap->get('json');
                    return $adapter;
                },
            'act'           =>  function ($jsonAdapter) {
                    $result = $jsonAdapter->adaptOut(['a', 'b', 'c' => 'ok'], 'array');
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result === ['a', 'b', 'c' => 'ok']);
                }
        ],
    '4111' => [
            'description'   =>  "SQL adapter OUT - password hashes clear value",
            'arrange'       =>  function () {
                    return new DataAdapterSqlText();
                },
            'act'           =>  function ($sqlAdapter) {
                    return $sqlAdapter->adaptOut('secure_password', 'password');
                },
            'assert'        =>  function($result) {
                    return (substr($result, 0, 4) === '$2y$' && password_verify('secure_password', $result));
                }
        ],
    '4112' => [
            'description'   =>  "SQL adapter OUT - password keeps encrypted value",
            'arrange'       =>  function () {
                    return [
                        new DataAdapterSqlText(),
                        password_hash('secure_password', PASSWORD_BCRYPT)
                    ];
                },
            'act'           =>  function ($params) {
                    [$sqlAdapter, $hash] = $params;
                    return [
                        $hash,
                        $sqlAdapter->adaptOut($hash, 'password')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result[1] === $result[0]);
                }
        ],
    '4113' => [
            'description'   =>  "TXT adapter OUT - phone",
            'act'           =>  function () {
                    $adapter = new DataAdapterTxt();
                    return [
                        $adapter->adaptOut('+32489532419', 'phone'),
                        $adapter->adaptOut(null, 'phone')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        DataFormatter::format('+32489532419', 'phone'),
                        null
                    ]);
                }
        ],
    '4114' => [
            'description'   =>  "TXT adapter OUT - email",
            'act'           =>  function () {
                    $adapter = new DataAdapterTxt();
                    return [
                        $adapter->adaptOut('User.Name+tag@Example.com', UsageFactory::create('email')),
                        $adapter->adaptOut(null, 'email')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === ['User.Name+tag@Example.com', null]);
                }
        ],
    '4115' => [
            'description'   =>  "JSON adapter OUT - canonical phone",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    return [
                        $adapter->adaptOut('+32478456789', 'phone'),
                        $adapter->adaptOut(null, 'phone')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        '+32478456789',
                        null
                    ]);
                }
        ],
    '4116' => [
            'description'   =>  "JSON adapter OUT - canonical email",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    return [
                        $adapter->adaptOut('User.Name+tag@Example.com', UsageFactory::create('email')),
                        $adapter->adaptOut(null, 'email')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === ['User.Name+tag@Example.com', null]);
                }
        ],
    '4117' => [
            'description'   =>  "Usage validation after phone adaptation",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    $phoneUsage = UsageFactory::create('phone');
                    $phone = $adapter->adaptIn('+32 (478) 45.67.89', $phoneUsage);
                    $phoneConstraint = $phoneUsage->getConstraints()['invalid_phone']['function'];
                    return $phoneConstraint($phone);
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '4118' => [
            'description'   =>  "Usage validation after email adaptation",
            'act'           =>  function () {
                    $adapter = new DataAdapterJson();
                    $emailUsage = UsageFactory::create('email');
                    $email = $adapter->adaptIn(' User.Name+tag@Example.com ', $emailUsage);
                    $emailConstraint = $emailUsage->getConstraints()['invalid_email']['function'];
                    return $emailConstraint($email);
                },
            'assert'        =>  function($result) {
                    return ($result === true);
                }
        ],
    '4119' => [
            'description'   =>  "DataFormatter: direct phone formatting compatibility",
            'act'           =>  function () {
                    return DataFormatter::format('+32489532419', 'phone');
                },
            'assert'        =>  function($result) {
                    return ($result === '+32 489 53 24 19');
                }
        ],
    '4120' => [
            'description'   =>  "SQL text adapter - canonical phone",
            'act'           =>  function () {
                    $adapter = new DataAdapterSqlText();
                    return [
                        $adapter->adaptIn('+32478456789', 'phone'),
                        $adapter->adaptOut('+32478456789', 'phone'),
                        $adapter->adaptOut(null, 'phone')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        '+32478456789',
                        '+32478456789',
                        null
                    ]);
                }
        ],
    '4121' => [
            'description'   =>  "SQL text adapter - canonical email",
            'act'           =>  function () {
                    $adapter = new DataAdapterSqlText();
                    return [
                        $adapter->adaptIn('User.Name+tag@Example.com', 'email'),
                        $adapter->adaptOut('User.Name+tag@Example.com', 'email'),
                        $adapter->adaptOut(null, 'email')
                    ];
                },
            'assert'        =>  function($result) {
                    return ($result === [
                        'User.Name+tag@Example.com',
                        'User.Name+tag@Example.com',
                        null
                    ]);
                }
        ]
];
