<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\orm\traits;

/**
 * Disables model discriminator filtering for the model using this trait.
 *
 * By default, a model that inherits its parent's table is scoped: the ORM adds
 * a condition on the `model` field so that the class only sees its own rows.
 * This remains true whether the parent owns the table itself or shares the
 * table of one of its ancestors.
 *
 * Returning null from `getModelScope()` removes that implicit condition. ORM
 * operations performed through this model may therefore address every row in
 * the shared table, including rows belonging to parent or sibling models. The
 * override is inherited, so descendants remain unscoped unless they provide
 * their own `getModelScope()` implementation.
 *
 * A class that defines a distinct table through `getModelTable()` is already
 * unscoped by the default `Model::getModelScope()` implementation. This trait
 * is intended for models that deliberately need the same effect while sharing
 * a table. In all cases, `getModelTable()` and `getModelScope()` may be
 * overridden together or independently for a more specific storage strategy.
 */
trait IsNotScoped {

    /**
     * Disables the implicit `model` discriminator for this model hierarchy.
     */
    public static function getModelScope(): ?string {
        return null;
    }
}
