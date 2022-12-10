    <?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageCurrency extends Usage {

    public function getConstraints(): array {
        if($this->getSubtype() == 'iso-4217.numeric') {
            return [
                'invalid_currency' => [
                    'message'   => 'Value is not a 3-digits country code (iso-3166-1).',
                    'function'  =>  function($value) {
                        return (in_array($value, ['004','008','010','012','016','020','024','028','031','032','036','040','044','048','050','051','052','056','060','064','068','070','072','074','076','084','086','090','092','096','100','104','108','112','116','120','124','132','136','140','144','148','152','156','158','162','166','170','174','175','178','180','184','188','191','192','196','203','204','208','212','214','218','222','226','231','232','233','234','238','239','242','246','248','250','254','258','260','262','266','268','270','275','','276','288','292','296','300','304','308','312','316','320','324','328','332','334','336','340','344','348','352','356','360','364','368','372','376','380','384','388','392','398','400','404','408','410','414','417','418','422','426','428','430','434','438','440','442','446','450','454','458','462','466','470','474','478','480','484','492','496','498','499','500','504','508','512','516','520','524','528','531','533','534','535','540','548','554','558','562','566','570','574','578','580','581','583','584','585','586','591','598','600','604','608','612','616','620','624','626','630','634','638','642','643','646','652','654','659','660','662','663','666','670','674','678','682','686','688','690','694','702','703','704','705','706','710','716','724','728','729','732','740','744','748','752','756','760','762','764','768','772','776','780','784','788','792','795','796','798','800','804','807','818','826','831','832','833','834','840','850','854','858','860','862','876','882','887','894']));
                    }
                ]
            ];
        }
        // subtype is expected to be iso-4217
        switch($this->getLength()) {
            case '0':
            case '3':
            default:
                return [
                    'invalid_currency' => [
                        'message'   => 'Value is not a 2-letters language code (iso-4217 alpha-3).',
                        'function'  =>  function($value) {
                            return (in_array($value, ['ADF','ADP','AED','AFA','AFN','ALL','AMD','ANG','AOA','AOK','AON','AOR','ARP','ARS','ATS','AUD','AWG','AZM','AZN','BAM','BBD','BDT','BEF','BGL','BGN','BHD','BIF','BMD','BND','BOB','BOP','BOV','BRL','BRR','BSD','BTN','BWP','BYB','BYR','BYN','BZD','CAD','CDF','CHE','CHF','CHW','CLF','CLP','CNY','COP','COU','CRC','CSD','CSK','CUC','CUP','CVE','CYP','CZK','DEM','DJF','DKK','DOP','DZD','ECS','ECV','EEK','EGP','ERN','ESP','ETB','EUR','FIM','FJD','FKP','FRF','GBP','GEL','GHS','GIP','GMD','GNF','GRD','GTQ','GWP','GYD','HKD','HNL','HRK','HTG','HUF','IDR','IEP','ILS','INR','IQD','IRR','ISK','ITL','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KZT','KWD','KYD','LAK','LBP','LKR','LRD','LSL','LTL','LUF','LVL','LVR','LYD','MAD','MDL','MGA','MGF','MKD','MMK','MNT','MOP','MRO','MRU','MTL','MUR','MVR','MWK','MXN','MXV','MYR','MZE','MZM','MZN','NAD','NGN','NHF','NIC','NIO','NLG','NOK','NPR','NZD','OMR','PAB','PEN','PES','PGK','PHP','PKR','PLN','PLZ','PTE','PYG','QAR','ROL','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDD','SDG','SDP','SEK','SGD','SHP','SIT','SKK','SLL','SML','SOS','SRD','SSP','STD','SUB','SUR','SVC','SYP','SZL','THB','TJS','TMM','TMT','TND','TOP','TPE','TRL','TRY','TTD','TWD','TZS','UAH','UGX','USD','USN','USS','UYU','UYW','UZS','VAL','VEB','VEF','VES','VND','VUV','WST','XAF','XAG','XAU','XBA','XBB','XBC','XBD','XCD','XDR','XEU','XFO','XFU','XOF','XPD','XPF','XPT','XSU','XUA','YER','YUD','YUM','ZAR','ZMK','ZWD','ZWL','ZWR']));
                        }
                    ]
                ];
        }
    }

    public function export($value, $lang='en'): string {
        return $value;
    }

}
