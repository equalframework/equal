<?php
namespace equal\text;


class TextTransformer {

    /**
    * Transforms given string to a standard ASCII string containing lowercase words separated by single spaces
    * (no accent, punctuation signs, quotes, plus nor dash)
    * @param    string  $value  UTF-8 string to convert to ASCII.
    * @return   string          Returns an ASCII-chars only string with no punctuation, that should be accepted by any system.
    */
    public static function normalize($value) {
        // #memo - remember to maintain current file charset to UTF-8 !
        $ascii = array(
            // lower case chars
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ä'=>'A', 'Ã'=>'A', 'Ā'=>'A', 'Ă'=>'A', 'Å'=>'A',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ẽ'=>'E', 'Ē'=>'E',
            'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ĩ'=>'I', 'Ī'=>'I',
            'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Ö'=>'O', 'Õ'=>'O', 'Ō'=>'O', 'Ő' => 'O',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ũ'=>'U', 'Ū'=>'U', 'Ű' => 'U',
            'Ý'=>'Y', 'Ỳ'=>'Y', 'Ŷ'=>'Y', 'Ÿ'=>'Y', 'Ỹ'=>'Y', 'Ȳ'=>'Y',
            'Æ'=>'A', 'Þ'=>'B', 'Ç'=>'C', 'Ð'=>'Dj','Ñ'=>'N', 'Ń'=>'N', 'Ø'=>'O', 'ß'=>'Ss', 'Š'=>'S', 'Ș'=>'S', 'Ț'=>'T', 'Ž'=>'Z',
            // upper case chars
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ä'=>'a', 'ã'=>'a', 'ā'=>'a', 'ă'=>'a', 'å'=>'a',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'ẽ'=>'e', 'ē'=>'e',
            'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ĩ'=>'i', 'ī'=>'i',
            'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'ö'=>'o', 'õ'=>'o', 'ō'=>'o', 'ő'=>'o',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ũ'=>'u', 'ū'=>'u', 'ű'=>'u',
            'ý'=>'y', 'ỳ'=>'y', 'ŷ'=>'y', 'ÿ'=>'y', 'ỹ'=>'y', 'ȳ'=>'y',
            'æ'=>'a', 'þ'=>'b', 'ç'=>'c', 'ƒ'=>'f', 'ñ'=>'n', 'ń'=>'n', 'ð'=>'o', 'ø'=>'o', 'š'=>'s', 'ș'=>'s', 'ț'=>'t', 'ž'=>'z'
        );
        $value = str_replace(array_keys($ascii), array_values($ascii), $value);
        // remove all non-[quote-space-alphanum-dash] chars
        $value = preg_replace('/[^-\'\sa-z0-9]/i', '', $value);
        // trim the end of the string
        $value = trim($value, ' .-_');
        return strtolower($value);
    }


   /**
    * Transform a string into a slug (URL-compatible words separated by dashes)
    * This method expects a UTF-8 string
    */
    public static function slugify($value, $max_length=255) {
        return substr(str_replace(' ', '-', self::normalize($value)), 0, $max_length);
    }

    /**
     * Cuts a string by words according to given max length.
     *
     * @param string    $value
     * @param integer   $max_length
     * @return string
     */
    public static function excerpt($value, $max_length) {
        $res = '';
        $len = 0;
        for($i = 0, $parts = explode(' ', $value), $j = count($parts); $i < $j; ++$i) {
            $piece = $parts[$i].' ';
            $p_len = strlen($piece);
            if($len + $p_len > $max_length) break;
            $len += $p_len;
            $res .= $piece;
        } if($len == 0) $res = substr($value, 0, $max_length);
        return $res;
    }

    /**
    * Try to convert a word to its most common form (masculin singulier ou verbe)
    */
    public static function axiomize($word, $locale='fr') {
        static $locales = [
        'fr' => [
            'eaux'  => 'eau',
            'aux'   => 'al',
            'eux'   => 'eu',
            'oux'   => 'ou',
            's'     => '',
            'onne'  => 'on',
            'ional' => 'ion',
            'nage'  => 'ner',
            'euse'  => 'eur',
            'rice'  => 'eur',
            'ere'   => 'er'
            ]
        ];
        $items = $locales[$locale];
        $word_len = strlen($word);
        foreach($items as $key => $val) {
            $key_len = strlen($key);
            // do not alter full-word
            if($word_len > $key_len) {
                if(substr($word, -$key_len) == $key) {
                    $word = substr($word, 0, -$key_len);
                    $word = $word.$val;
                    $word_len = strlen($word);
                    break;
                }
            }
        }
        return $word;
    }

    public static function is_relevant($word, $locale='fr') {
        static $locales = [
        'en' => ["a", "one", "the", "of", "it", "its", "is", "has", "have", "when", "with", "what", "that", "from", "there", "for", "thus"],
        'fr' => ["un", "une", "le", "la", "les", "l", "d", "de", "du", "des", "ce", "ca", "ces", "c", "s", "que", "qui", "quoi", "qu", "est", "es", "a", "au", "ou", "il", "elle", "pour", "donc", "dont"]
        ];
        if(!isset( $locales[$locale])) {
            return (strlen($word) >= 3 );
        }
        $items = $locales[$locale];
        return !(strlen($word) < 3 || in_array($word, $items));
    }


    /**
     * Generate a 64-bits integer hash from given string
     * returned value is intended to be stored in a UNISGNED BIGINT DBMS column (8 bytes/20 digits)
     *
     * @return  string  20 digits hash
     */
    public static function hash($value) {
        return gmp_strval(gmp_init(substr(md5($value), 0, 16), 16), 10);
    }
}