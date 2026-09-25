<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\orm\traits;

/**
 * Gives the model using this trait its own storage table.
 *
 * By default, a model that extends another concrete model keeps the same table
 * as its parent. The ORM then scopes that model with the `model` discriminator,
 * whether the parent owns that table or already shares an ancestor's table.
 * This prevents operations on the child model from targeting sibling records
 * stored in the same table.
 *
 * Defining `getModelTable()` establishes a new storage boundary. This trait
 * returns the slug of the class in which it is used, so that table differs from
 * the parent's table. The default `Model::getModelScope()` consequently returns
 * null for that class: it is automatically unscoped and ORM operations can
 * address the whole of its own table without a `model` condition.
 *
 * Descendants inherit this table. Since they share it with their parent, they
 * are scoped by default and only address rows carrying their own discriminator.
 * A model may always override `getModelTable()` and/or `getModelScope()` when it
 * needs a specific table-sharing or discriminator strategy.
 */
trait HasOwnTable {

    abstract public static function getSlug(?string $class=null): string;

    /**
     * Returns the dedicated table introduced by the class using this trait.
     *
     * `self::class` deliberately identifies the class where the trait is used,
     * while `static::getSlug()` keeps late-static binding available to callers.
     * Inherited calls therefore keep this table until a descendant overrides
     * `getModelTable()` to introduce another storage boundary.
     */
    public static function getModelTable(): string {
        return static::getSlug(self::class);
    }
}
