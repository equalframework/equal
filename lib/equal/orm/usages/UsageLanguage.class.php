<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageLanguage extends Usage {

    private $three_letters_langue_codes = ['aar','abk','ace','ach','ada','ady','afa','afh','afr','ain','aka','akk','alb','sqi','ale','alg','alt','amh','ang','anp','apa','ara','arc','arg','arm','hye','arn','arp','art','arw','asm','ast','ath','aus','ava','ave','awa','aym','aze','bad','bai','bak','bal','bam','ban','baq','eus','bas','bat','bej','bel','bem','ben','ber','bho','bih','bik','bin','bis','bla','bnt','tib','bod','bos','bra','bre','btk','bua','bug','bul','bur','mya','byn','cad','cai','car','cat','cau','ceb','cel','cze','ces','cha','chb','che','chg','chi','zho','chk','chm','chn','cho','chp','chr','chu','chv','chy','cmc','cnr','cop','cor','cos','cpe','cpf','cpp','cre','crh','crp','csb','cus','wel','cym','cze','ces','dak','dan','dar','day','del','den','ger','deu','dgr','din','div','doi','dra','dsb','dua','dum','dut','nld','dyu','dzo','efi','egy','eka','gre','ell','elx','eng','enm','epo','est','baq','eus','ewe','ewo','fan','fao','per','fas','fat','fij','fil','fin','fiu','fon','fra','fre','frm','fro','frr','frs','fry','ful','fur','gaa','gay','gba','gem','geo','kat','ger','deu','gez','gil','gla','gle','glg','glv','gmh','goh','gon','gor','got','grb','grc','gre','ell','grn','gsw','guj','gwi','hai','hat','hau','haw','heb','her','hil','him','hin','hit','hmn','hmo','hrv','hsb','hun','hup','arm','hye','iba','ibo','ice','isl','ido','iii','ijo','iku','ile','ilo','ina','inc','ind','ine','inh','ipk','ira','iro','ice','isl','ita','jav','jbo','jpn','jpr','jrb','kaa','kab','kac','kal','kam','kan','kar','kas','geo','kat','kau','kaw','kaz','kbd','kha','khi','khm','kho','kik','kin','kir','kmb','kok','kom','kon','kor','kos','kpe','krc','krl','kro','kru','kua','kum','kur','kut','lad','lah','lam','lao','lat','lav','lez','lim','lin','lit','lol','loz','ltz','lua','lub','lug','lui','lun','luo','lus','mac','mkd','mad','mag','mah','mai','mak','mal','man','mao','mri','map','mar','mas','may','msa','mdf','mdr','men','mga','mic','min','mis','mac','mkd','mkh','mlg','mlt','mnc','mni','mno','moh','mon','mos','mao','mri','may','msa','mul','mun','mus','mwl','mwr','bur','mya','myn','myv','nah','nai','nap','nau','nav','nbl','nde','ndo','nds','nep','new','nia','nic','niu','dut','nld','nno','nob','nog','non','nor','nqo','nso','nub','nwc','nya','nym','nyn','nyo','nzi','oci','oji','ori','orm','osa','oss','ota','oto','paa','pag','pal','pam','pan','pap','pau','peo','per','fas','phi','phn','pli','pol','pon','por','pra','pro','pus','qaa','que','raj','rap','rar','roa','roh','rom','rum','ron','rum','ron','run','rup','rus','sad','sag','sah','sai','sal','sam','san','sas','sat','scn','sco','sel','sem','sga','sgn','shn','sid','sin','sio','sit','sla','slo','slk','slo','slk','slv','sma','sme','smi','smj','smn','smo','sms','sna','snd','snk','sog','som','son','sot','spa','alb','sqi','srd','srn','srp','srr','ssa','ssw','suk','sun','sus','sux','swa','swe','syc','syr','tah','tai','tam','tat','tel','tem','ter','tet','tgk','tgl','tha','tib','bod','tig','tir','tiv','tkl','tlh','tli','tmh','tog','ton','tpi','tsi','tsn','tso','tuk','tum','tup','tur','tut','tvl','twi','tyv','udm','uga','uig','ukr','umb','und','urd','uzb','vai','ven','vie','vol','vot','wak','wal','war','was','wel','cym','wen','wln','wol','xal','xho','yao','yap','yid','yor','ypk','zap','zbl','zen','zgh','zha','chi','zho','znd','zul','zun','zxx','zza'];

    private $two_letters_language_codes = ['aa','ab','af','ak','sq','am','ar','an','hy','as','av','ae','ay','az','ba','bm','eu','be','bn','bh','bi','bo','bs','br','bg','my','ca','cs','ch','ce','zh','cu','cv','kw','co','cr','cy','cs','da','de','dv','nl','dz','el','en','eo','et','eu','ee','fo','fa','fj','fi','fr','fy','ff','ka','de','gd','ga','gl','gv','el','gn','gu','ht','ha','he','hz','hi','ho','hr','hu','hy','ig','is','io','ii','iu','ie','ia','id','ik','is','it','jv','ja','kl','kn','ks','ka','kr','kk','km','ki','rw','ky','kv','kg','ko','kj','ku','lo','la','lv','li','ln','lt','lb','lu','lg','mk','mh','ml','mi','mr','ms','mk','mg','mt','mn','mi','ms','my','na','nv','nr','nd','ng','ne','nl','nn','nb','no','ny','oc','oj','or','om','os','pa','fa','pi','pl','pt','ps','qu','rm','ro','ro','rn','ru','sg','sa','si','sk','sk','sl','se','sm','sn','sd','so','st','es','sq','sc','sr','ss','su','sw','sv','ty','ta','tt','te','tg','tl','th','bo','ti','to','tn','ts','tk','tr','tw','ug','uk','ur','uz','ve','vi','vo','cy','wa','wo','xh','yi','yo','za','zh','zu'];

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            $this->length = 2;
        }
    }

    public function getConstraints(): array {
        // subtype is expected to be iso-639
        switch($this->getLength()) {
            case '3':
                return [
                    'invalid_language' => [
                        'message'   => 'Value is not a 3-letters language code (iso-639-2 alpha-3).',
                        'function'  =>  function($value) {
                            return (in_array($value, $this->three_letters_langue_codes));
                        }
                    ]
                ];
            case '0':
            case '2':
            default:
                return [
                    'invalid_language' => [
                        'message'   => 'Value is not a 2-letters language code (iso-639-1 alpha-2).',
                        'function'  =>  function($value) {
                            return (in_array($value, $this->two_letters_language_codes));
                        }
                    ]
                ];
        }
    }

    public function generateRandomValue(): string {
        $language_codes = $this->getLength() === 3 ? $this->three_letters_langue_codes : $this->two_letters_language_codes;

        return $language_codes[array_rand($language_codes)];
    }

}
