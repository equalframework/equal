<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace {

    use core\test\LifecycleConsistencyProbe;
    use core\test\LifecycleReadonlyRequiredProbe;
    use core\test\LifecycleProbe;
    use core\test\Test;
    use equal\orm\ObjectManager;

    $tests = [

        '5001' => [
                'description' => 'Lifecycle: Test::create assigns instance state by default.',
                'act'         => function () {
                        return Test::create(['string_short' => 'create'])
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['state'] ?? null) === 'instance';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            Test::id($id)->delete(true);
                        }
                    }
            ],

        '5002' => [
                'description' => 'Lifecycle: Test::create preserves explicit draft state.',
                'act'         => function () {
                        return Test::create(['state' => 'draft'])
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['state'] ?? null) === 'draft';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            Test::id($id)->delete(true);
                        }
                    }
            ],

        '5003' => [
                'description' => 'Lifecycle: Test::id()->update instantiates a draft.',
                'arrange'     => function () {
                        $test = Test::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        return Test::id($id)
                            ->update(['string_short' => 'inst'])
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['state'] ?? null) === 'instance';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            Test::id($id)->delete(true);
                        }
                    }
            ],

        '5004' => [
                'description' => 'Lifecycle: Test::id()->update keeps instance state.',
                'arrange'     => function () {
                        $test = Test::create(['string_short' => 'before'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        return Test::id($id)
                            ->update(['string_short' => 'after'])
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['state'] ?? null) === 'instance';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            Test::id($id)->delete(true);
                        }
                    }
            ],

        '5005' => [
                'description' => 'Lifecycle: direct instance creation invokes oncreate then onafterinstantiate.',
                'act'         => function () {
                        LifecycleProbe::resetLifecycleEvents();

                        $test = LifecycleProbe::create(['string_short' => 'create'])
                            ->read(['id'])
                            ->first();

                        return $test['id'] ?? null;
                    },
                'assert'      => function($result) {
                        $events = LifecycleProbe::getLifecycleEvents();
                        $hooks = array_column($events, 'hook');

                        return $result > 0
                            && $hooks === ['oncreate', 'onafterinstantiate'];
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5006' => [
                'description' => 'Lifecycle: draft update invokes instantiate hooks.',
                'arrange'     => function () {
                        LifecycleProbe::resetLifecycleEvents();

                        $test = LifecycleProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        LifecycleProbe::id($id)
                            ->update(['string_short' => 'inst']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $hooks = array_column(LifecycleProbe::getLifecycleEvents(), 'hook');

                        return $hooks === ['oncreate', 'onbeforeinstantiate', 'onafterinstantiate'];
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5007' => [
                'description' => 'Lifecycle: instance update invokes update hooks.',
                'arrange'     => function () {
                        $test = LifecycleProbe::create(['string_short' => 'before'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        LifecycleProbe::id($id)
                            ->update(['string_short' => 'after']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $events = array_slice(LifecycleProbe::getLifecycleEvents(), -2);
                        $hooks = array_column($events, 'hook');

                        return $hooks === ['onbeforeupdate', 'onafterupdate'];
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5008' => [
                'description' => 'Lifecycle consistency: draft creation does not require mandatory fields.',
                'act'         => function () {
                        return LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['state'] ?? null) === 'draft';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5009' => [
                'description' => 'Lifecycle consistency: instantiating a draft requires mandatory fields.',
                'arrange'     => function () {
                        $test = LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        $result = [
                            'id'      => $id,
                            'error'   => 0,
                            'details' => []
                        ];

                        try {
                            LifecycleConsistencyProbe::id($id)
                                ->update(['state' => 'instance']);
                        }
                        catch(Exception $e) {
                            $result['error'] = $e->getCode();
                            $details = @unserialize($e->getMessage());
                            $result['details'] = is_array($details) ? $details : [];
                        }

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['error'] ?? 0) === EQ_ERROR_MISSING_PARAM
                            && ($result['details']['string_short']['missing_mandatory'] ?? null) === 'Missing mandatory parameter.';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5010' => [
                'description' => 'Lifecycle compatibility: ObjectManager::update allows a legacy duplicate instantiation.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();
                        $value = 'u'.substr(md5((string) microtime(true)), 0, 8);

                        $draft_id = $om->create($class, [
                                'state'        => 'draft',
                                'string_short' => $value
                            ], null, false);

                        $instance_id = $om->create($class, [
                                'string_short' => $value
                            ], null, false);

                        return [
                            'value'       => $value,
                            'draft_id'    => $draft_id,
                            'instance_id' => $instance_id
                        ];
                    },
                'act'         => function ($fixtures) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();
                        $result = $fixtures;
                        $result['update'] = $om->update($class, [$fixtures['draft_id']], ['state' => 'instance']);

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['update'] ?? null) === [$result['draft_id']];
                    },
                'rollback'    => function($result) {
                        $ids = [];
                        if(($result['draft_id'] ?? 0) > 0) {
                            $ids[] = $result['draft_id'];
                        }
                        if(($result['instance_id'] ?? 0) > 0) {
                            $ids[] = $result['instance_id'];
                        }
                        if(count($ids)) {
                            LifecycleConsistencyProbe::ids($ids)->delete(true);
                        }
                    }
            ],
        '5011' => [
                'description' => 'Lifecycle compatibility: ObjectManager::create inserts before returning a required-field error.',
                'arrange'     => function () {
                        $ids = ObjectManager::getInstance()->search(
                            LifecycleConsistencyProbe::getType(),
                            [
                                ['state', 'in', ['draft', 'instance']],
                                ['deleted', 'in', ['0', '1']]
                            ]
                        );

                        return count($ids) ? max(array_map('intval', $ids)) : 0;
                    },
                'act'         => function ($last_id) {
                        $om = ObjectManager::getInstance();

                        return [
                            'last_id'    => $last_id,
                            'result'     => $om->create(LifecycleConsistencyProbe::getType(), []),
                            'last_error' => $om->getLastError()
                        ];
                    },
                'assert'      => function($result) {
                        $ids = ObjectManager::getInstance()->search(
                            LifecycleConsistencyProbe::getType(),
                            [
                                ['id', '>', $result['last_id'] ?? 0],
                                ['deleted', 'in', ['0', '1']]
                            ]
                        );
                        $details = @unserialize($result['last_error'] ?? '');

                        return ($result['result'] ?? 0) === EQ_ERROR_MISSING_PARAM
                            && is_array($details)
                            && ($details['string_short']['missing_mandatory'] ?? null) === 'Missing mandatory parameter.'
                            && count($ids) === 1;
                    },
                'rollback'    => function($result) {
                        $ids = ObjectManager::getInstance()->search(
                            LifecycleConsistencyProbe::getType(),
                            [
                                ['id', '>', $result['last_id'] ?? 0],
                                ['deleted', 'in', ['0', '1']]
                            ]
                        );

                        if(count($ids)) {
                            LifecycleConsistencyProbe::ids($ids)->delete(true);
                        }
                    }
            ],

        '5012' => [
                'description' => 'Lifecycle consistency: ObjectManager::update refuses instantiation without required fields.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        return $om->create(LifecycleConsistencyProbe::getType(), ['state' => 'draft']);
                    },
                'act'         => function ($id) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();

                        return [
                            'id'         => $id,
                            'update'     => $om->update($class, [$id], ['state' => 'instance']),
                            'last_error' => $om->getLastError()
                        ];
                    },
                'assert'      => function($result) {
                        $details = @unserialize($result['last_error'] ?? '');

                        return ($result['update'] ?? 0) === EQ_ERROR_MISSING_PARAM
                            && is_array($details)
                            && ($details['string_short']['missing_mandatory'] ?? null) === 'Missing mandatory parameter.';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],
        '5013' => [
                'description' => 'Lifecycle consistency: draft instantiation accepts a required readonly value.',
                'arrange'     => function () {
                        $test = LifecycleReadonlyRequiredProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        return LifecycleReadonlyRequiredProbe::id($id)
                            ->update(['string_short' => 'required'])
                            ->read(['id', 'state', 'string_short'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['state'] ?? null) === 'instance'
                            && ($result['string_short'] ?? null) === 'required';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleReadonlyRequiredProbe::id($id)->delete(true);
                        }
                    }
            ],
        '5014' => [
                'description' => 'Lifecycle consistency: ObjectManager::update ignores missing ids.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleProbe::getType();

                        $alive_id = $om->create($class, ['string_short' => 'alive']);
                        $hard_id = $om->create($class, ['string_short' => 'hard']);

                        $om->delete($class, [$hard_id], true);

                        return [
                            'alive_id' => $alive_id,
                            'hard_id'  => $hard_id
                        ];
                    },
                'act'         => function ($fixtures) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleProbe::getType();

                        return $om->update(
                            $class,
                            [$fixtures['alive_id'], $fixtures['hard_id']],
                            ['string_short' => 'updated']
                        );
                    },
                'assert'      => function($result) {
                        return count($result) === 1
                            && $result[0] > 0;
                    },
                'rollback'    => function($result) {
                        $id = $result[0] ?? 0;
                        if($id > 0) {
                            LifecycleProbe::id($id)->delete(true);
                        }
                    }
            ],
        '5015' => [
                'description' => 'Lifecycle: ObjectManager::draft/instantiate assigns an id and promotes the draft.',
                'arrange'     => function () {
                        return ObjectManager::getInstance()
                            ->draft(LifecycleProbe::getType());
                    },
                'act'         => function ($id) {
                        return ObjectManager::getInstance()
                            ->instantiate(LifecycleProbe::getType(), [$id]);
                    },
                'assert'      => function($result) {
                        $id = $result[0] ?? 0;
                        $test = LifecycleProbe::id($id)
                            ->read(['state'])
                            ->first();

                        return $id > 0
                            && ($test['state'] ?? null) === 'instance';
                    },
                'rollback'    => function($result) {
                        $id = $result[0] ?? 0;
                        if($id > 0) {
                            LifecycleProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5016' => [
                'description' => 'Lifecycle: Collection::draft invokes oncreate and instantiate promotes the draft.',
                'arrange'     => function () {
                        LifecycleProbe::resetLifecycleEvents();

                        $test = LifecycleProbe::draft()
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        return LifecycleProbe::id($id)
                            ->instantiate()
                            ->read(['id', 'state'])
                            ->first();
                    },
                'assert'      => function($result) {
                        $hooks = array_column(LifecycleProbe::getLifecycleEvents(), 'hook');

                        return ($result['id'] ?? 0) > 0
                            && ($result['state'] ?? null) === 'instance'
                            && $hooks === ['oncreate', 'onafterinstantiate'];
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5017' => [
                'description' => 'Lifecycle consistency: ObjectManager::update restores a soft-deleted record.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleProbe::getType();
                        $id = $om->create($class, ['string_short' => 'soft']);

                        $om->delete($class, [$id], false);

                        return $id;
                    },
                'act'         => function ($id) {
                        return ObjectManager::getInstance()->update(
                            LifecycleProbe::getType(),
                            [$id],
                            ['deleted' => 0]
                        );
                    },
                'assert'      => function($result) {
                        $id = $result[0] ?? 0;
                        $test = LifecycleProbe::id($id)
                            ->read(['deleted'])
                            ->first();

                        return (bool) ($test['deleted'] ?? true) === false;
                    },
                'rollback'    => function($result) {
                        $id = $result[0] ?? 0;
                        if($id > 0) {
                            LifecycleProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5018' => [
                'description' => 'Lifecycle consistency: draft creation accepts an explicit null required value.',
                'act'         => function () {
                        return LifecycleConsistencyProbe::create([
                                'state'        => 'draft',
                                'string_short' => null
                            ])
                            ->read(['id', 'state', 'string_short'])
                            ->first();
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['state'] ?? null) === 'draft'
                            && ($result['string_short'] ?? null) === null;
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5019' => [
                'description' => 'Lifecycle consistency: draft update reports an explicit null mandatory field.',
                'arrange'     => function () {
                        $test = LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        $result = [
                            'id'      => $id,
                            'error'   => 0,
                            'details' => []
                        ];

                        try {
                            LifecycleConsistencyProbe::id($id)
                                ->update(['string_short' => null]);
                        }
                        catch(Exception $e) {
                            $result['error'] = $e->getCode();
                            $details = @unserialize($e->getMessage());
                            $result['details'] = is_array($details) ? $details : [];
                        }

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['error'] ?? 0) === EQ_ERROR_INVALID_PARAM
                            && ($result['details']['string_short']['null_mandatory'] ?? null) === 'Mandatory field cannot be null.';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5020' => [
                'description' => 'Lifecycle consistency: explicit instantiation reports a missing mandatory field.',
                'arrange'     => function () {
                        $test = LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        $result = [
                            'id'      => $id,
                            'error'   => 0,
                            'details' => []
                        ];

                        try {
                            LifecycleConsistencyProbe::id($id)->instantiate();
                        }
                        catch(Exception $e) {
                            $result['error'] = $e->getCode();
                            $details = @unserialize($e->getMessage());
                            $result['details'] = is_array($details) ? $details : [];
                        }

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['error'] ?? 0) === EQ_ERROR_MISSING_PARAM
                            && ($result['details']['string_short']['missing_mandatory'] ?? null) === 'Missing mandatory parameter.';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5021' => [
                'description' => 'Lifecycle: ORM and Collection clones refresh audit timestamps (`created` and `modified`).',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleProbe::getType();
                        $old_timestamp = time() - 3600;
                        $source_id = $om->create($class, [
                                'string_short' => 'source',
                                'created'      => $old_timestamp
                            ], null, false);

                        $om->update($class, [$source_id], ['modified' => $old_timestamp], null, false, false);

                        return [
                            'source_id'     => $source_id,
                            'old_timestamp' => $old_timestamp
                        ];
                    },
                'act'         => function ($fixtures) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleProbe::getType();
                        $before_clone = time();
                        $orm_clone_id = $om->clone($class, $fixtures['source_id']);

                        $collection = LifecycleProbe::id($fixtures['source_id'])->clone();
                        $collection_clone_ids = array_values(array_diff($collection->ids(), [$fixtures['source_id']]));
                        $collection_clone_id = $collection_clone_ids[0] ?? 0;
                        $after_clone = time();

                        $clones = LifecycleProbe::ids([$orm_clone_id, $collection_clone_id])
                            ->read(['created', 'modified'])
                            ->toArray();

                        return array_merge($fixtures, [
                            'clone_ids'    => [$orm_clone_id, $collection_clone_id],
                            'before_clone' => $before_clone,
                            'after_clone'  => $after_clone,
                            'clones'       => $clones
                        ]);
                    },
                'assert'      => function($result) {
                        if(count($result['clones'] ?? []) !== 2) {
                            return false;
                        }

                        foreach($result['clones'] as $clone) {
                            $created = (int) ($clone['created'] ?? 0);
                            $modified = (int) ($clone['modified'] ?? 0);

                            if($created <= $result['old_timestamp']
                                || $created < $result['before_clone']
                                || $created > $result['after_clone']
                                || $modified <= $result['old_timestamp']
                                || $modified < $result['before_clone']
                                || $modified > $result['after_clone']) {
                                return false;
                            }
                        }

                        return true;
                    },
                'rollback'    => function($result) {
                        $ids = array_filter(array_merge(
                            [$result['source_id'] ?? 0],
                            $result['clone_ids'] ?? []
                        ));

                        if(count($ids)) {
                            LifecycleProbe::ids($ids)->delete(true);
                        }
                    }
            ],
    ];
}
