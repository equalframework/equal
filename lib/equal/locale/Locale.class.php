<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\locale;


class Locale {

/**
     * Retrieve the term from a locale for a given package and a given section.
     *
     * @param   string      $package    Package to which the setting relates to.
     * @param   string      $section    Name of the section the item relates to.
     * @param   string      $item       Name of the item to be translated (id from the section map).
     * @param   mixed       $default    (optional) Default value to return if term is not found.
     * @param   string      $lang       (optional) Lang in which to retrieve the value (for multilang settings).
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get(string $package, string $section, string $item, $default=null, string $lang='en') {
        $result = $default;

        $schema = [];

        if(isset($GLOBALS['_equal_core_lang_cache'][$package][$lang])) {
            $schema = $GLOBALS['_equal_core_lang_cache'][$package][$lang];
        }
        else {
            // extract `$language`_`$country`.`$codeset`
            $parts = explode('.', $lang);
            // `language`_`country`
            $locale = $parts[0];
            $parts = explode('_', $locale);
            // `language`
            $language = $parts[0];

            $names = [$lang, $locale, $language];
            // find first applicable i18n file (more precision first)
            foreach($names as $name) {
                $file = QN_BASEDIR."/packages/{$package}/i18n/$name/locale.json";
                if(file_exists($file)) {
                    $schema = json_decode(@file_get_contents($file), true);
                    $GLOBALS['_equal_core_lang_cache'][$package][$lang] = $schema;
                    break;
                }
            }
        }

        if(isset($schema[$section]) && isset($schema[$section][$item])) {
            $result = $schema[$section][$item];
        }

        return $result;
    }

    /**
     * Retrieve the term from a locale for a given package.
     *
     * @param   string      $package    Package to which the setting relates to.
     * @param   string      $term       Name of the term to be translated (id from the `terms` map).
     * @param   mixed       $default    (optional) Default value to return if term is not found.
     * @param   string      $lang       (optional) Lang in which to retrieve the value (for multilang settings).
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_term(string $package, string $term, $default=null, string $lang='en') {
        return self::get($package, 'terms', $term, $default, $lang);
    }

    /**
     * Retrieve the format from a locale for a given package.
     *
     * @param   string      $package    Package to which the setting relates to.
     * @param   string      $format     Name of the format to be retrieved (id from the `formats` map).
     * @param   mixed       $default    (optional) Default value to return if format is not found.
     * @param   string      $lang       (optional) Lang in which to retrieve the value (for multilang settings).
     * @return  mixed       Returns the value of the target setting or null if the setting parameter is not found. The type of the returned var depends on the setting's `type` field.
     */
    public static function get_format(string $package, string $format, $default=null, string $lang='en') {
        return self::get($package, 'formats', $format, $default, $lang);
    }
}
