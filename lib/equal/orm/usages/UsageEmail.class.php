    <?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageImage extends Usage {

    public function getType(): string {
        return 'image';
    }

    public function getSqlType(): string {
            return 'longblob';
    }

    /**
     *
     *        supports:
     *            c@a.com
     *            c@a.b.com
     *            c@a.b.c.com
     *            a+c@a.b.c.com
     *            a.c+d@a.b.c.com
     *            ad@a.b.c.xn--vermgensberatung-pwb
     *        does not support:
     *            a+c.d@a.b.c.com
     */
    public function getConstraints(): array {
        return [
            'invalid_email' => [
                'message'   => 'Malformed email address.',
                'function'  =>  function($value) {
                    return (bool) (preg_match('/^([_a-z0-9-\.]+)(\+([_a-z0-9]+))?@(([a-z0-9-]+\.)*)([a-z0-9-]{1,63})(\.[a-z-]{2,24})$/', $value));
                }
            ]
        ];
    }

    public function export($value, $lang=DEFAULT_LANG): string {
        return $value;
    }

}
