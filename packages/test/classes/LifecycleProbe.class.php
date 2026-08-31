<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace test;

class LifecycleProbe extends Test
{
    private static $lifecycle_events = [];

    public static function resetLifecycleEvents() {
        self::$lifecycle_events = [];
    }

    public static function getLifecycleEvents() {
        return self::$lifecycle_events;
    }

    private static function recordLifecycleEvent($hook, $self, $ids, $values) {
        self::$lifecycle_events[] = [
            'hook'     => $hook,
            'ids'      => array_values($ids),
            'self_ids' => array_values($self->ids()),
            'values'   => array_intersect_key($values, array_flip(['state', 'string_short']))
        ];
    }

    public static function oncreate($self, $ids, $values) {
        self::recordLifecycleEvent('oncreate', $self, $ids, $values);
    }

    public static function onafterinstantiate($self, $ids, $values) {
        self::recordLifecycleEvent('onafterinstantiate', $self, $ids, $values);
    }

    public static function onbeforeinstantiate($self, $ids, $values) {
        self::recordLifecycleEvent('onbeforeinstantiate', $self, $ids, $values);
    }

    public static function onbeforeupdate($self, $ids, $values) {
        self::recordLifecycleEvent('onbeforeupdate', $self, $ids, $values);
    }

    public static function onafterupdate($self, $ids, $values) {
        self::recordLifecycleEvent('onafterupdate', $self, $ids, $values);
    }
}
