<?php
namespace equal\data;

use equal\organic\Service;

class DataValidator extends Service {
    
    /**
     * Provide a structure corresponding to the given usage.
     * In case no constraint is associated to the usage, an always-valid constraint is returned.
     * 
     * @param   $usage  string  Identifier of the usage for which the constraint is requested.
     * @return  array
     */
    public static function getConstraintFromUsage($usage) {
        switch($usage) {
            case 'language/iso-639':
            case 'language/iso-639:2': 
                return [
                    'kind'  => 'function', 
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['aa','ab','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','ba','bm','eu','be','bn','bh','bi','bo','bs','br','bg','my','ca','cs','ch','ce','zh','cu','cv','kw','co','cr','cy','cs','da','de','dv','nl','dz','el','en','eo','et','eu','ee','fo','fa','fj','fi','fr','fr','fy','ff','ka','de','gd','ga','gl','gv','el','gn','gu','ht','ha','he','hz','hi','ho','hr','hu','hy','ig','is','io','ii','iu','ie','ia','id','ik','is','it','jv','ja','kl','kn','ks','ka','kr','kk','km','ki','rw','ky','kv','kg','ko','kj','ku','lo','la','lv','li','ln','lt','lb','lu','lg','mk','mh','ml','mi','mr','ms','mk','mg','mt','mn','mi','ms','my','na','nv','nr','nd','ng','ne','nl','nn','nb','no','ny','oc','oj','or','om','os','pa','fa','pi','pl','pt','ps','qu','rm','ro','ro','rn','ru','sg','sa','si','sk','sk','sl','se','sm','sn','sd','so','st','es','sq','sc','sr','ss','su','sw','sv','ty','ta','tt','te','tg','tl','th','bo','ti','to','tn','ts','tk','tr','tw','ug','uk','ur','uz','ve','vi','vo','cy','wa','wo','xh','yi','yo','za','zh','zu']));
                    }                    
                ];
            case 'language/iso-639:3': 
                return [
                    'kind'  => 'function', 
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['aar','abk','ace','ach','ada','ady','afa','afh','afr','ain','aka','akk','alb','sqi','ale','alg','alt','amh','ang','anp','apa','ara','arc','arg','arm','hye','arn','arp','art','arw','asm','ast','ath','aus','ava','ave','awa','aym','aze','bad','bai','bak','bal','bam','ban','baq','eus','bas','bat','bej','bel','bem','ben','ber','bho','bih','bik','bin','bis','bla','bnt','tib','bod','bos','bra','bre','btk','bua','bug','bul','bur','mya','byn','cad','cai','car','cat','cau','ceb','cel','cze','ces','cha','chb','che','chg','chi','zho','chk','chm','chn','cho','chp','chr','chu','chv','chy','cmc','cnr','cop','cor','cos','cpe','cpf','cpp','cre','crh','crp','csb','cus','wel','cym','cze','ces','dak','dan','dar','day','del','den','ger','deu','dgr','din','div','doi','dra','dsb','dua','dum','dut','nld','dyu','dzo','efi','egy','eka','gre','ell','elx','eng','enm','epo','est','baq','eus','ewe','ewo','fan','fao','per','fas','fat','fij','fil','fin','fiu','fon','fre','fra','fre','fra','frm','fro','frr','frs','fry','ful','fur','gaa','gay','gba','gem','geo','kat','ger','deu','gez','gil','gla','gle','glg','glv','gmh','goh','gon','gor','got','grb','grc','gre','ell','grn','gsw','guj','gwi','hai','hat','hau','haw','heb','her','hil','him','hin','hit','hmn','hmo','hrv','hsb','hun','hup','arm','hye','iba','ibo','ice','isl','ido','iii','ijo','iku','ile','ilo','ina','inc','ind','ine','inh','ipk','ira','iro','ice','isl','ita','jav','jbo','jpn','jpr','jrb','kaa','kab','kac','kal','kam','kan','kar','kas','geo','kat','kau','kaw','kaz','kbd','kha','khi','khm','kho','kik','kin','kir','kmb','kok','kom','kon','kor','kos','kpe','krc','krl','kro','kru','kua','kum','kur','kut','lad','lah','lam','lao','lat','lav','lez','lim','lin','lit','lol','loz','ltz','lua','lub','lug','lui','lun','luo','lus','mac','mkd','mad','mag','mah','mai','mak','mal','man','mao','mri','map','mar','mas','may','msa','mdf','mdr','men','mga','mic','min','mis','mac','mkd','mkh','mlg','mlt','mnc','mni','mno','moh','mon','mos','mao','mri','may','msa','mul','mun','mus','mwl','mwr','bur','mya','myn','myv','nah','nai','nap','nau','nav','nbl','nde','ndo','nds','nep','new','nia','nic','niu','dut','nld','nno','nob','nog','non','nor','nqo','nso','nub','nwc','nya','nym','nyn','nyo','nzi','oci','oji','ori','orm','osa','oss','ota','oto','paa','pag','pal','pam','pan','pap','pau','peo','per','fas','phi','phn','pli','pol','pon','por','pra','pro','pus','qaa','que','raj','rap','rar','roa','roh','rom','rum','ron','rum','ron','run','rup','rus','sad','sag','sah','sai','sal','sam','san','sas','sat','scn','sco','sel','sem','sga','sgn','shn','sid','sin','sio','sit','sla','slo','slk','slo','slk','slv','sma','sme','smi','smj','smn','smo','sms','sna','snd','snk','sog','som','son','sot','spa','alb','sqi','srd','srn','srp','srr','ssa','ssw','suk','sun','sus','sux','swa','swe','syc','syr','tah','tai','tam','tat','tel','tem','ter','tet','tgk','tgl','tha','tib','bod','tig','tir','tiv','tkl','tlh','tli','tmh','tog','ton','tpi','tsi','tsn','tso','tuk','tum','tup','tur','tut','tvl','twi','tyv','udm','uga','uig','ukr','umb','und','urd','uzb','vai','ven','vie','vol','vot','wak','wal','war','was','wel','cym','wen','wln','wol','xal','xho','yao','yap','yid','yor','ypk','zap','zbl','zen','zgh','zha','chi','zho','znd','zul','zun','zxx','zza']));
                    }
                ];
            case 'email':
            case 'url/mailto':
            case 'uri/url:mailto':
                return [
                    'kind'  => 'function', 
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9+-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $a));}
                ];
            case 'currency/iso-4217': 
            case 'currency/iso-4217:alpha':
                return [
                    'kind'  => 'function', 
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['ADF','ADP','AED','AFA','AFN','ALL','AMD','ANG','AOA','AOK','AON','AOR','ARP','ARS','ATS','AUD','AWG','AZM','AZN','BAM','BBD','BDT','BEF','BGL','BGN','BHD','BIF','BMD','BND','BOB','BOP','BOV','BRL','BRR','BSD','BTN','BWP','BYB','BYR','BYN','BZD','CAD','CDF','CHE','CHF','CHW','CLF','CLP','CNY','COP','COU','CRC','CSD','CSK','CUC','CUP','CVE','CYP','CZK','DEM','DJF','DKK','DOP','DZD','ECS','ECV','EEK','EGP','ERN','ESP','ETB','EUR','FIM','FJD','FKP','FRF','GBP','GEL','GHS','GIP','GMD','GNF','GRD','GTQ','GWP','GYD','HKD','HNL','HRK','HTG','HUF','IDR','IEP','ILS','INR','IQD','IRR','ISK','ITL','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KZT','KWD','KYD','LAK','LBP','LKR','LRD','LSL','LTL','LUF','LVL','LVR','LYD','MAD','MDL','MGA','MGF','MKD','MMK','MNT','MOP','MRO','MRU','MTL','MUR','MVR','MWK','MXN','MXV','MYR','MZE','MZM','MZN','NAD','NGN','NHF','NIC','NIO','NLG','NOK','NPR','NZD','OMR','PAB','PEN','PES','PGK','PHP','PKR','PLN','PLZ','PTE','PYG','QAR','ROL','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDD','SDG','SDP','SEK','SGD','SHP','SIT','SKK','SLL','SML','SOS','SRD','SSP','STD','SUB','SUR','SVC','SYP','SZL','THB','TJS','TMM','TMT','TND','TOP','TPE','TRL','TRY','TTD','TWD','TZS','UAH','UGX','USD','USN','USS','UYU','UYW','UZS','VAL','VEB','VEF','VES','VND','VUV','WST','XAF','XAG','XAU','XBA','XBB','XBC','XBD','XCD','XDR','XEU','XFO','XFU','XOF','XPD','XPF','XPT','XSU','XUA','YER','YUD','YUM','ZAR','ZMK','ZWD','ZWL','ZWR']));
                    }
                ];
            case 'password':
                return [
                    'description' => 'Password having a length of 8 chars minimum.',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return strlen($a) >= 8;
                    }
                ];
            case 'password/NIST':
                return [
                    'description' => 'NIST compliant password (min. 8 chars, 1 of @#$, 1 numeric digit, 1 uppercase, 1 lowercase).',
                    'kind'  => 'function', 
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$#])[A-Za-z\d@$#]{8,}$/', $a));}
                ];                
        }
        return [
            'kind'  => 'function', 
            'rule'  => function($a, $o) { return true; }
        ];
    }

    /**
     * Service constructor.
     * This method cannot be called directly (should be invoked through Singleton::getInstance)
     * the value will be returned unchanged if:
     * - a conversion is not explicitely defined
     * - a conversion cannot be made
     */
    protected function __construct(/* no dependency */) {
        // initial configuration
    }
    
    /**  
     * Tells if given $value comply with related $constraints set.
     *
     * Accepted elementary types are: 'boolean' (or 'bool'), 'integer' (or 'int'), 'double' (or 'float'), 'string', 'array', 'file'
     * 'file' is a pseudo type whic covers PHP file structure from multipart/form-data, base64 encoded binary value
     *
     * Constraints is an array holding constraints description specific to the given value 
     * it is an array of validation rules, each rule consist of a kind
     * - type (boolean, integer, double, string, date, array)
     * - 'min', 'max', 'in', 'not in'
     * - 'regex' or 'pattern'
     * - (custom) function (any callable accepting one parameter and returning a boolean)
     *
     * and the description of the rule itself
     *
     * examples:
     * [ ['kind' => 'function', 'rule' => function($a) {return !($a%2);}],
     *   ['kind' => 'min', 'rule' => 0 ],      
     *   ['kind' => 'in', 'rule' => [1, 2, 3] ]     
     * ]
     *
     */
    public function validate($value, $constraints) {        
        if(!is_array($constraints) || empty($constraints)) return true;
        foreach($constraints as $id => $constraint) {
            // ignore empty constraints            
            if(!isset($constraint['kind']) || !isset($constraint['rule'])) {
                continue;
            }
            switch($constraint['kind']) {
            case 'type':
                // fix alternate names to the expected value
                foreach(['bool' => 'boolean', 'int' => 'integer', 'float' => 'double', 'text' => 'string'] as $key => $type) {
                    if($constraint['rule'] == $key) {
                        $constraint['rule'] = $type;
                        break;
                    }
                }
                // $value type should be amongst elementary PHP types
                if(!in_array($constraint['rule'], ['boolean', 'integer', 'double', 'string', 'date', 'datetime', 'array', 'file'])) {
                    throw new \Exception("Invalid type {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if($constraint['rule'] == 'file') {
                    if(!in_array(gettype($value), ['string', 'array'])) return false;
                }
                // dates are handled as Unix timestamps
                else if($constraint['rule'] == 'date' || $constraint['rule'] == 'datetime') {                    
                    if(!gettype($value) == 'integer') return false;
                }
                else if(gettype($value) != $constraint['rule']) return false;
                break;
            case 'pattern':
            case 'regex':
                if(!preg_match("/^\/.+\/[a-z]*$/i", $constraint['rule'])) {
                    throw new \Exception("Invalid pattern {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if(!preg_match($constraint['rule'], $value)) return false;
                break;                
            case 'function':            
                if(!is_callable($constraint['rule'])) {
                    throw new \Exception("Unknown function {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                if(call_user_func($constraint['rule'], $value) !== true) return false;
                break;
            case 'min':
                if(!is_numeric($constraint['rule'])) {
                    throw new \Exception("Non numeric min constraint {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                switch(gettype($value)) {
                case 'string':
                    if(strlen($value) < $constraint['rule']) return false;
                    break;                        
                case 'integer':
                case 'double':                    
                    if($value < $constraint['rule']) return false;
                    break;
                case 'array': 
                    if(count($value) < $constraint['rule']) return false;
                    break;
                default:
                    // error : unhandled value type for contraint 'min'
                    break;
                }
                break;
            case 'max':
                if(!is_numeric($constraint['rule'])) {
                    throw new \Exception("Non numeric max constraint {$constraint['rule']}", QN_ERROR_INVALID_CONFIG);
                }
                switch(gettype($value)) {
                case 'string':
                    if(strlen($value) > $constraint['rule']) return false;
                    break;                        
                case 'integer':
                case 'double':                    
                    if($value > $constraint['rule']) return false;
                    break;
                case 'array': 
                    if(count($value) > $constraint['rule']) return false;
                    break;
                default:
                    // error : unhandled value type for contraint 'max'
                    break;
                }
                break;
            case 'in':
            case 'not in':            
                if(!is_array($constraint['rule'])) {
                    // warning : 'in' and 'not in' constraint has to be array
                    // try to force conversion to array
                    $constraint['rule'] = [$constraint['rule']];                    
                }
                $type = gettype($value);
                if($type == 'string') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_string($accept)) {
                            // error : while checking a string 'in' constraint has to hold string values
                            unset($constraint['rule'][$index]);
                        }
                    }
                }
                else if ($type == 'integer') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_integer($accept)) {
                            // error : while checking an integer 'in' constraint has to hold integer values
                            unset($constraint['rule'][$index]);
                        }
                    }
                }
                else if ($type == 'double') {
                    foreach($constraint['rule'] as $index => $accept) {
                        if(!is_integer($accept) && !is_double($accept)) {
                            // error : while checking a float/double 'in' constraint has to hold float values
                            unset($constraint['rule'][$index]);
                        }
                    }                
                }
                else {                
                    // error : unhandled value type for contraint 'max'
                    continue 2;
                }
                if(in_array($value, $constraint['rule'])) {
                    if($constraint['kind'] == 'not in') return false;
                }
                else if($constraint['kind'] == 'in') return false;
                break;
            default:
                // warning : unhandled constraint type {$constraint['kind']}
                break;                        
            }
        }
        return true;
    }
}