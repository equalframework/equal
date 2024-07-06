<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm\usages;


class UsageCountry extends Usage {

    public function __construct(string $usage_str) {
        parent::__construct($usage_str);
        if($this->length == 0) {
            $this->length = 3;
        }
    }

    public function getConstraints(): array {
        if($this->getSubtype() == 'country.numeric') {
            return [
                'invalid_country' => [
                    'message'   => 'Value is not a 3-digits country code (iso-3166-1 numeric).',
                    'function'  =>  function($value) {
                        return (in_array($value, ['004','008','010','012','016','020','024','028','031','032','036','040','044','048','050','051','052','056','060','064','068','070','072','074','076','084','086','090','092','096','100','104','108','112','116','120','124','132','136','140','144','148','152','156','158','162','166','170','174','175','178','180','184','188','191','192','196','203','204','208','212','214','218','222','226','231','232','233','234','238','239','242','246','248','250','254','258','260','262','266','268','270','275','','276','288','292','296','300','304','308','312','316','320','324','328','332','334','336','340','344','348','352','356','360','364','368','372','376','380','384','388','392','398','400','404','408','410','414','417','418','422','426','428','430','434','438','440','442','446','450','454','458','462','466','470','474','478','480','484','492','496','498','499','500','504','508','512','516','520','524','528','531','533','534','535','540','548','554','558','562','566','570','574','578','580','581','583','584','585','586','591','598','600','604','608','612','616','620','624','626','630','634','638','642','643','646','652','654','659','660','662','663','666','670','674','678','682','686','688','690','694','702','703','704','705','706','710','716','724','728','729','732','740','744','748','752','756','760','762','764','768','772','776','780','784','788','792','795','796','798','800','804','807','818','826','831','832','833','834','840','850','854','858','860','862','876','882','887','894']));
                    }
                ]
            ];
        }
        // subtype is expected to be ISO-3166
        switch($this->getLength()) {
            case '3':
                return [
                    'invalid_country' => [
                        'message'   => 'Value is not a 3-letters country code (iso-3166-1 alpha-3).',
                        'function'  =>  function($value) {
                            return (in_array($value, ['ABW','AFG','AGO','AIA','ALA','ALB','AND','ARE','ARG','ARM','ASM','ATA','ATF','ATG','AUS','AUT','AZE','BDI','BEL','BEN','BES','BFA','BGD','BGR','BHR','BHS','BIH','BLM','BLR','BLZ','BMU','BOL','BRA','BRB','BRN','BTN','BVT','BWA','CAF','CAN','CCK','CHE','CHL','CHN','CIV','CMR','COD','COG','COK','COL','COM','CPV','CRI','CUB','CUW','CXR','CYM','CYP','CZE','DEU','DJI','DMA','DNK','DOM','DZA','ECU','EGY','ERI','ESH','ESP','EST','ETH','FIN','FJI','FLK','FRA','FRO','FSM','GAB','GBR','GEO','GGY','GHA','GIB','GIN','GLP','GMB','GNB','GNQ','GRC','GRD','GRL','GTM','GUF','GUM','GUY','HKG','HMD','HND','HRV','HTI','HUN','IDN','IMN','IND','IOT','IRL','IRN','IRQ','ISL','ISR','ITA','JAM','JEY','JOR','JPN','KAZ','KEN','KGZ','KHM','KIR','KNA','KOR','KWT','LAO','LBN','LBR','LBY','LCA','LIE','LKA','LSO','LTU','LUX','LVA','MAC','MAF','MAR','MCO','MDA','MDG','MDV','MEX','MHL','MKD','MLI','MLT','MMR','MNE','MNG','MNP','MOZ','MRT','MSR','MTQ','MUS','MWI','MYS','MYT','NAM','NCL','NER','NFK','NGA','NIC','NIU','NLD','NOR','NPL','NRU','NZL','OMN','PAK','PAN','PCN','PER','PHL','PLW','PNG','POL','PRI','PRK','PRT','PRY','PSE','PYF','QAT','REU','ROU','RUS','RWA','SAU','SDN','SEN','SGP','SGS','SHN','SJM','SLB','SLE','SLV','SMR','SOM','SPM','SRB','SSD','STP','SUR','SVK','SVN','SWE','SWZ','SXM','SYC','SYR','TCA','TCD','TGO','THA','TJK','TKL','TKM','TLS','TON','TTO','TUN','TUR','TUV','TWN','TZA','UGA','UKR','UMI','URY','USA','UZB','VAT','VCT','VEN','VGB','VIR','VNM','VUT','WLF','WSM','YEM','ZAF','ZMB','ZWE']));
                        }
                    ]
                ];
            case '0':
            case '2':
            default:
                return [
                    'invalid_country' => [
                        'message'   => 'Value is not a 2-letters country code (iso-3166-1 alpha-2).',
                        'function'  =>  function($value) {
                            return (in_array($value, ['AF','AX','AL','DZ','AS','AD','AO','AI','AQ','AG','AR','AM','AW','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BM','BT','BO','BA','BW','BV','BR','IO','BN','BG','BF','BI','KH','CM','CA','CV','KY','CF','TD','CL','CN','CX','CC','CO','KM','CG','CD','CK','CR','CI','HR','CU','CY','CZ','DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FK','FO','FJ','FI','FR','GF','PF','TF','GA','GM','GE','DE','GH','GI','GR','GL','GD','GP','GU','GT','GG','GN','GW','GY','HT','HM','VA','HN','HK','HU','IS','IN','ID','IR','IQ','IE','IM','IL','IT','JM','JP','JE','JO','KZ','KE','KI','KR','KW','KG','LA','LV','LB','LS','LR','LY','LI','LT','LU','MO','MK','MG','MW','MY','MV','ML','MT','MH','MQ','MR','MU','YT','MX','FM','MD','MC','MN','ME','MS','MA','MZ','MM','NA','NR','NP','NL','AN','NC','NZ','NI','NE','NG','NU','NF','MP','NO','OM','PK','PW','PS','PA','PG','PY','PE','PH','PN','PL','PT','PR','QA','RE','RO','RU','RW','BL','SH','KN','LC','MF','PM','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','GS','ES','LK','SD','SR','SJ','SZ','SE','CH','SY','TW','TJ','TZ','TH','TL','TG','TK','TO','TT','TN','TR','TM','TC','TV','UG','UA','AE','GB','US','UM','UY','UZ','VU','VE','VN','VG','VI','WF','EH','YE','ZM','ZW']));
                        }
                    ]
                ];
        }
    }

}
