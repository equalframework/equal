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
            // used for locale : accept IETF BCP 47 language tags
            case 'language/iso-639':
            case 'language/iso-639:2':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        // countries codes from ISO 3166-1
                        $countries = ['AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UM','UY','UZ','VU','VE','VN','VG','VI','WF','EH','YE','ZM','ZW'];
                        // languages codes from ISO 639-1
                        $langs = ['aa','ab','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','ba','bm','eu','be','bn','bh','bi','bo','bs','br','bg','my','ca','cs','ch','ce','zh','cu','cv','kw','co','cr','cy','cs','da','de','dv','nl','dz','el','en','eo','et','eu','ee','fo','fa','fj','fi','fr','fr','fy','ff','ka','de','gd','ga','gl','gv','el','gn','gu','ht','ha','he','hz','hi','ho','hr','hu','hy','ig','is','io','ii','iu','ie','ia','id','ik','is','it','jv','ja','kl','kn','ks','ka','kr','kk','km','ki','rw','ky','kv','kg','ko','kj','ku','lo','la','lv','li','ln','lt','lb','lu','lg','mk','mh','ml','mi','mr','ms','mk','mg','mt','mn','mi','ms','my','na','nv','nr','nd','ng','ne','nl','nn','nb','no','ny','oc','oj','or','om','os','pa','fa','pi','pl','pt','ps','qu','rm','ro','ro','rn','ru','sg','sa','si','sk','sk','sl','se','sm','sn','sd','so','st','es','sq','sc','sr','ss','su','sw','sv','ty','ta','tt','te','tg','tl','th','bo','ti','to','tn','ts','tk','tr','tw','ug','uk','ur','uz','ve','vi','vo','cy','wa','wo','xh','yi','yo','za','zh','zu'];
                        if(strpos($a, '-') > 0) {
                            $parts = explode('-', $a);
                            return (in_array($parts[0], $langs)) && (in_array($parts[1], $countries));
                        }
                        return (in_array($a, $langs));
                    }
                ];
            case 'language/iso-639:3':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['aar','abk','ace','ach','ada','ady','afa','afh','afr','ain','aka','akk','alb','sqi','ale','alg','alt','amh','ang','anp','apa','ara','arc','arg','arm','hye','arn','arp','art','arw','asm','ast','ath','aus','ava','ave','awa','aym','aze','bad','bai','bak','bal','bam','ban','baq','eus','bas','bat','bej','bel','bem','ben','ber','bho','bih','bik','bin','bis','bla','bnt','tib','bod','bos','bra','bre','btk','bua','bug','bul','bur','mya','byn','cad','cai','car','cat','cau','ceb','cel','cze','ces','cha','chb','che','chg','chi','zho','chk','chm','chn','cho','chp','chr','chu','chv','chy','cmc','cnr','cop','cor','cos','cpe','cpf','cpp','cre','crh','crp','csb','cus','wel','cym','cze','ces','dak','dan','dar','day','del','den','ger','deu','dgr','din','div','doi','dra','dsb','dua','dum','dut','nld','dyu','dzo','efi','egy','eka','gre','ell','elx','eng','enm','epo','est','baq','eus','ewe','ewo','fan','fao','per','fas','fat','fij','fil','fin','fiu','fon','fre','fra','fre','fra','frm','fro','frr','frs','fry','ful','fur','gaa','gay','gba','gem','geo','kat','ger','deu','gez','gil','gla','gle','glg','glv','gmh','goh','gon','gor','got','grb','grc','gre','ell','grn','gsw','guj','gwi','hai','hat','hau','haw','heb','her','hil','him','hin','hit','hmn','hmo','hrv','hsb','hun','hup','arm','hye','iba','ibo','ice','isl','ido','iii','ijo','iku','ile','ilo','ina','inc','ind','ine','inh','ipk','ira','iro','ice','isl','ita','jav','jbo','jpn','jpr','jrb','kaa','kab','kac','kal','kam','kan','kar','kas','geo','kat','kau','kaw','kaz','kbd','kha','khi','khm','kho','kik','kin','kir','kmb','kok','kom','kon','kor','kos','kpe','krc','krl','kro','kru','kua','kum','kur','kut','lad','lah','lam','lao','lat','lav','lez','lim','lin','lit','lol','loz','ltz','lua','lub','lug','lui','lun','luo','lus','mac','mkd','mad','mag','mah','mai','mak','mal','man','mao','mri','map','mar','mas','may','msa','mdf','mdr','men','mga','mic','min','mis','mac','mkd','mkh','mlg','mlt','mnc','mni','mno','moh','mon','mos','mao','mri','may','msa','mul','mun','mus','mwl','mwr','bur','mya','myn','myv','nah','nai','nap','nau','nav','nbl','nde','ndo','nds','nep','new','nia','nic','niu','dut','nld','nno','nob','nog','non','nor','nqo','nso','nub','nwc','nya','nym','nyn','nyo','nzi','oci','oji','ori','orm','osa','oss','ota','oto','paa','pag','pal','pam','pan','pap','pau','peo','per','fas','phi','phn','pli','pol','pon','por','pra','pro','pus','qaa','que','raj','rap','rar','roa','roh','rom','rum','ron','rum','ron','run','rup','rus','sad','sag','sah','sai','sal','sam','san','sas','sat','scn','sco','sel','sem','sga','sgn','shn','sid','sin','sio','sit','sla','slo','slk','slo','slk','slv','sma','sme','smi','smj','smn','smo','sms','sna','snd','snk','sog','som','son','sot','spa','alb','sqi','srd','srn','srp','srr','ssa','ssw','suk','sun','sus','sux','swa','swe','syc','syr','tah','tai','tam','tat','tel','tem','ter','tet','tgk','tgl','tha','tib','bod','tig','tir','tiv','tkl','tlh','tli','tmh','tog','ton','tpi','tsi','tsn','tso','tuk','tum','tup','tur','tut','tvl','twi','tyv','udm','uga','uig','ukr','umb','und','urd','uzb','vai','ven','vie','vol','vot','wak','wal','war','was','wel','cym','wen','wln','wol','xal','xho','yao','yap','yid','yor','ypk','zap','zbl','zen','zgh','zha','chi','zho','znd','zul','zun','zxx','zza']));
                    }
                ];
            case 'phone':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^((\+[1-9]{2,3})|00)?[0-9]+$/', $a));}
                ];
            case 'email':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^([_a-z0-9-]+)(\.[_a-z0-9+-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $a));}
                ];
            case 'uri/url.tel':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^tel:((\+[1-9]{2,3})|00)?[0-9]+$/', $a));}
                ];
            case 'uri/url.mailto':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^mailto:([_a-z0-9-]+)(\.[_a-z0-9+-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,13})$/', $a));}
                ];
            case 'currency/iso-4217':
            case 'currency/iso-4217.alpha':
                return [
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['ADF','ADP','AED','AFA','AFN','ALL','AMD','ANG','AOA','AOK','AON','AOR','ARP','ARS','ATS','AUD','AWG','AZM','AZN','BAM','BBD','BDT','BEF','BGL','BGN','BHD','BIF','BMD','BND','BOB','BOP','BOV','BRL','BRR','BSD','BTN','BWP','BYB','BYR','BYN','BZD','CAD','CDF','CHE','CHF','CHW','CLF','CLP','CNY','COP','COU','CRC','CSD','CSK','CUC','CUP','CVE','CYP','CZK','DEM','DJF','DKK','DOP','DZD','ECS','ECV','EEK','EGP','ERN','ESP','ETB','EUR','FIM','FJD','FKP','FRF','GBP','GEL','GHS','GIP','GMD','GNF','GRD','GTQ','GWP','GYD','HKD','HNL','HRK','HTG','HUF','IDR','IEP','ILS','INR','IQD','IRR','ISK','ITL','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KZT','KWD','KYD','LAK','LBP','LKR','LRD','LSL','LTL','LUF','LVL','LVR','LYD','MAD','MDL','MGA','MGF','MKD','MMK','MNT','MOP','MRO','MRU','MTL','MUR','MVR','MWK','MXN','MXV','MYR','MZE','MZM','MZN','NAD','NGN','NHF','NIC','NIO','NLG','NOK','NPR','NZD','OMR','PAB','PEN','PES','PGK','PHP','PKR','PLN','PLZ','PTE','PYG','QAR','ROL','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDD','SDG','SDP','SEK','SGD','SHP','SIT','SKK','SLL','SML','SOS','SRD','SSP','STD','SUB','SUR','SVC','SYP','SZL','THB','TJS','TMM','TMT','TND','TOP','TPE','TRL','TRY','TTD','TWD','TZS','UAH','UGX','USD','USN','USS','UYU','UYW','UZS','VAL','VEB','VEF','VES','VND','VUV','WST','XAF','XAG','XAU','XBA','XBB','XBC','XBD','XCD','XDR','XEU','XFO','XFU','XOF','XPD','XPF','XPT','XSU','XUA','YER','YUD','YUM','ZAR','ZMK','ZWD','ZWL','ZWR']));
                    }
                ];
            case 'currency/iso-4217.numeric':
            case 'country/iso-3166.numeric':
                return [
                    'description' => '3-digits country code (iso-3166-1).',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['004','008','010','012','016','020','024','028','031','032','036','040','044','048','050','051','052','056','060','064','068','070','072','074','076','084','086','090','092','096','100','104','108','112','116','120','124','132','136','140','144','148','152','156','158','162','166','170','174','175','178','180','184','188','191','192','196','203','204','208','212','214','218','222','226','231','232','233','234','238','239','242','246','248','250','254','258','260','262','266','268','270','275','','276','288','292','296','300','304','308','312','316','320','324','328','332','334','336','340','344','348','352','356','360','364','368','372','376','380','384','388','392','398','400','404','408','410','414','417','418','422','426','428','430','434','438','440','442','446','450','454','458','462','466','470','474','478','480','484','492','496','498','499','500','504','508','512','516','520','524','528','531','533','534','535','540','548','554','558','562','566','570','574','578','580','581','583','584','585','586','591','598','600','604','608','612','616','620','624','626','630','634','638','642','643','646','652','654','659','660','662','663','666','670','674','678','682','686','688','690','694','702','703','704','705','706','710','716','724','728','729','732','740','744','748','752','756','760','762','764','768','772','776','780','784','788','792','795','796','798','800','804','807','818','826','831','832','833','834','840','850','854','858','860','862','876','882','887','894']));
                    }
                ];
            case 'country/iso-3166:2':
                return [
                    'description' => '2-letters country code iso-3166-1.',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UM','UY','UZ','VU','VE','VN','VG','VI','WF','EH','YE','ZM','ZW']));
                    }
                ];
            case 'country/iso-3166:3':
                return [
                    'description' => '3-letters country code (iso-3166-1).',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return (in_array($a, ['ABW','AFG','AGO','AIA','ALA','ALB','AND','ARE','ARG','ARM','ASM','ATA','ATF','ATG','AUS','AUT','AZE','BDI','BEL','BEN','BES','BFA','BGD','BGR','BHR','BHS','BIH','BLM','BLR','BLZ','BMU','BOL','BRA','BRB','BRN','BTN','BVT','BWA','CAF','CAN','CCK','CHE','CHL','CHN','CIV','CMR','COD','COG','COK','COL','COM','CPV','CRI','CUB','CUW','CXR','CYM','CYP','CZE','DEU','DJI','DMA','DNK','DOM','DZA','ECU','EGY','ERI','ESH','ESP','EST','ETH','FIN','FJI','FLK','FRA','FRO','FSM','GAB','GBR','GEO','GGY','GHA','GIB','GIN','GLP','GMB','GNB','GNQ','GRC','GRD','GRL','GTM','GUF','GUM','GUY','HKG','HMD','HND','HRV','HTI','HUN','IDN','IMN','IND','IOT','IRL','IRN','IRQ','ISL','ISR','ITA','JAM','JEY','JOR','JPN','KAZ','KEN','KGZ','KHM','KIR','KNA','KOR','KWT','LAO','LBN','LBR','LBY','LCA','LIE','LKA','LSO','LTU','LUX','LVA','MAC','MAF','MAR','MCO','MDA','MDG','MDV','MEX','MHL','MKD','MLI','MLT','MMR','MNE','MNG','MNP','MOZ','MRT','MSR','MTQ','MUS','MWI','MYS','MYT','NAM','NCL','NER','NFK','NGA','NIC','NIU','NLD','NOR','NPL','NRU','NZL','OMN','PAK','PAN','PCN','PER','PHL','PLW','PNG','POL','PRI','PRK','PRT','PRY','PSE','PYF','QAT','REU','ROU','RUS','RWA','SAU','SDN','SEN','SGP','SGS','SHN','SJM','SLB','SLE','SLV','SMR','SOM','SPM','SRB','SSD','STP','SUR','SVK','SVN','SWE','SWZ','SXM','SYC','SYR','TCA','TCD','TGO','THA','TJK','TKL','TKM','TLS','TON','TTO','TUN','TUR','TUV','TWN','TZA','UGA','UKR','UMI','URY','USA','UZB','VAT','VCT','VEN','VGB','VIR','VNM','VUT','WLF','WSM','YEM','ZAF','ZMB','ZWE']));
                    }
                ];
            case 'string/password':
            case 'password':
                return [
                    'description' => 'Password having a length of 8 chars minimum.',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {
                        return strlen($a) >= 8;
                    }
                ];
            case 'password/nist':
                return [
                    'description' => 'NIST compliant password (min. 8 chars, 1 of @#$, 1 numeric digit, 1 uppercase, 1 lowercase).',
                    'kind'  => 'function',
                    'rule'  => function($a, $o) {return (bool) (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$#])[A-Za-z\d@$#]{8,}$/', $a));}
                ];
        }

        // with support for both bytes length and (multibytes) chars length (notation: chars_length.bytes_length)
        // #todo - allow size notations ':medium' (16MB) and ':long' (4GB)
        if(preg_match('/text\/plain(:([0-9]{1,5})(\.([0-9]{1,2}))?)?/', $usage, $out)) {
            $max = 65535;
            $chars_len = $max;
            $bytes_len = $max;
            /** @var array */
            $res = $out;
            if( is_array($res) && count($res) > 2) {
                $chars_len = intval($res[2]);
                $bytes_len = $chars_len;
                if(count($res) > 4) {
                    $bytes_len = $res[4];
                }
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($chars_len, $bytes_len) { return (mb_strlen($a) <= $chars_len) && (strlen($a) <= $bytes_len);}
            ];
        }

        if(preg_match('/string\/alpha(:([0-9]{1,3}))?/', $usage, $out)) {
            $len = 1;
            /** @var array */
            $res = $out;
            if( is_array($res) && count($res) > 2) {
                $len = $res[2];
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($len) { return (preg_match('/^[a-zA-Z]{0,'.$len.'}$/', (string) $a));}
            ];
        }

        if(preg_match('/amount\/money(:([0-9]{1,2}))?/', $usage, $out)) {
            $decimals = 2;
            /** @var array */
            $res = $out;
            if( is_array($res) && count($res) > 2) {
                $decimals = $res[2];
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($decimals) { return (preg_match('/^[+-]?[0-9]+(\.?[0-9]{0,'.$decimals.'})$/', (string) $a));}
            ];
        }

        if(preg_match('/numeric\/integer(:([0-9]{1,2}))?/', $usage, $out)) {
            $len = 18;
            /** @var array */
            $res = $out;
            if( is_array($res) && count($res) > 2) {
                $len = $res[2];
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($len) { return (preg_match('/^[+-]?[0-9]{0,'.$len.'}$/', (string) $a));}
            ];
        }

        if(preg_match('/numeric\/real(:([0-9]{1,2})(\.([0-9]{1,2}))?)?/', $usage, $out)) {
            // support 'precision.scale' length format
            $precision = 18;
            $scale = 0;
            /** @var array */
            $res = $out;
            if(is_array($res) && count($res) > 2) {
                $precision = $res[2];
                if(count($res) > 4) {
                    $scale = $res[4];
                }
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($precision, $scale) {
                    if($scale > $precision) return false;
                    $integers = $precision - $scale;
                    return (preg_match('/^[+-]?[0-9]{0,'.$integers.'}}(\.?[0-9]{0,'.$scale.'})$/', (string) $a));
                }
            ];
        }

        if(preg_match('/numeric\/hexadecimal(:([0-9]{1,2}))?/', $usage, $out)) {
            $length = 32;
            /** @var array */
            $res = $out;
            if( is_array($res) && count($res) > 2) {
                $length = $res[2];
            }
            return [
                'kind'  => 'function',
                'rule'  => function($a, $o) use($length) { return (preg_match('/^[0-9A-F]{0,'.$length.'}$/', (string) $a));}
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
                foreach([
                    'bool'      => 'boolean',
                    'int'       => 'integer',
                    'float'     => 'double',
                    'text'      => 'string',
                    'binary'    => 'string',
                    'many2one'  => 'integer',
                    'one2many'  => 'array',
                    'many2many' => 'array'
                ] as $key => $type) {
                    if($constraint['rule'] == $key) {
                        $constraint['rule'] = $type;
                        break;
                    }
                }
                // #todo - sync definitions from ObjectManager
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
            case 'selection':
                $constraint['kind'] = 'in';
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