<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\fields;

use equal\locale\Locale;

class FieldInteger extends Field {

    public function __construct(array $descriptor) {
        $this->type = 'integer';
        // parent::__construct($descriptor);
    }


}
