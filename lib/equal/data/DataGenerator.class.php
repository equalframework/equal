<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\data;

use equal\orm\UsageFactory;

class DataGenerator {

    /**
     * @param string    $field              Field name.
     * @param string    $field_descriptor   Field descriptor.
     * @param string    $lang               Lang code of the language in which the value must be generated (for multilang fields).
     * @return array|bool|float|int|mixed|string|null
     */
    public static function generateFromField(string $field, array $field_descriptor, string $lang = null) {
        if(isset($field_descriptor['usage'])) {
            try {
                $usage = UsageFactory::create($field_descriptor['usage']);
                return $usage->generateRandomValue();
            }
            catch(\Exception $e) {
                // Usage problem
            }
        }

        switch($field) {
            case 'username':
                return self::username();
            case 'firstname':
                return self::firstname($lang);
            case 'lastname':
                return self::lastname($lang);
            case 'fullname':
                return self::fullname($lang);
            case 'legal_name':
                return self::legalName($lang);
            case 'address_street':
                return self::addressStreet($lang);
            case 'address_dispatch':
                return self::addressDispatch($lang);
            case 'address_zip':
                return self::addressZip();
            case 'address_city':
                return self::addressCity($lang);
            case 'address_state':
                return self::addressState();
            case 'address_country':
                return self::addressCountry($lang);
            case 'address':
                return self::address($lang);
        }

        switch($field_descriptor['type']) {
            case 'string':
                if(!empty($field_descriptor['selection'])) {
                    if(isset($field_descriptor['selection'][0])) {
                        $values = array_values($field_descriptor['selection']);
                    }
                    else {
                        $values = array_keys($field_descriptor['selection']);
                    }
                    return $values[array_rand($values)];
                }
                elseif(isset($field_descriptor['default'])) {
                    return $field_descriptor['default'];
                }
                return self::plainText();
            case 'boolean':
                return self::boolean();
            case 'integer':
                return self::integerByLength(9);
            case 'float':
                return self::realNumberByLength(9, 2);
        }

        return null;
    }

    public static function plainText($min = 0, $max = 255): string {
        $words = [
            'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur',
            'adipiscing', 'elit', 'sed', 'do', 'eiusmod', 'tempor',
            'incididunt', 'ut', 'labore', 'et', 'dolore', 'magna',
            'aliqua', 'ut', 'enim', 'ad', 'minim', 'veniam', 'quis',
            'nostrud', 'exercitation', 'ullamco', 'laboris', 'nisi',
            'ut', 'aliquip', 'ex', 'ea', 'commodo', 'consequat'
        ];

        $generate_sentence = function() use ($words) {
            $sentence_length = mt_rand(6, 12);
            $sentence = [];
            for ($i = 0; $i < $sentence_length; $i++) {
                $sentence[] = $words[array_rand($words)];
            }
            return ucfirst(implode(' ', $sentence)) . '.';
        };

        $random_length = mt_rand($min, $max);
        $random_text = '';

        while (strlen($random_text) < $random_length) {
            $paragraph_length = mt_rand(3, 7);
            $paragraph = '';
            for ($i = 0; $i < $paragraph_length; $i++) {
                $paragraph .= $generate_sentence() . ' ';
            }
            $random_text .= trim($paragraph) . "\n\n";
        }

        return trim(substr($random_text, 0, $random_length));
    }

    public static function boolean($probability = 0.5): bool {
        $probability = max(0, min(1, $probability));

        return mt_rand() / mt_getrandmax() < $probability;
    }

    public static function integerByLength(int $length): int {
        $min = (pow(10, $length) - 1) * -1;
        $max = pow(10, $length) - 1;

        return mt_rand($min, $max);
    }

    public static function integer(int $min, int $max): int {
        return mt_rand($min, $max);
    }

    public static function realNumberByLength(int $precision, int $scale): float {
        $max_int_part = pow(10, $precision) - 1;
        $min_int_part = -$max_int_part;

        $int_part = mt_rand($min_int_part, $max_int_part);

        $fractional_part = mt_rand(0, pow(10, $scale) - 1) / pow(10, $scale);

        $random_float = $int_part + $fractional_part;

        return round($random_float, $scale);
    }

    public static function realNumber(float $min, float $max, int $decimals): float {
        $scale = pow(10, $decimals);

        return mt_rand($min * $scale, $max * $scale) / $scale;
    }

    public static function hexadecimal(int $length): string {
        $num_bytes = ceil($length / 2);
        $random_bytes = random_bytes($num_bytes);
        $hexadecimal_string = bin2hex($random_bytes);

        return substr($hexadecimal_string, 0, $length);
    }

    public static function email(): string {
        $domains = [
            'example.com', 'test.com', 'demo.com', 'sample.org', 'mywebsite.net',
            'mail.com', 'webmail.org', 'inbox.net', 'mailservice.io', 'emailprovider.co',
            'messaging.com', 'mailbox.org', 'fastmail.com', 'corpmail.net', 'freemail.org',
            'onlinemail.io', 'postbox.com', 'securemail.net', 'cloudmail.org', 'hostmail.co',
            'netmail.com', 'simplemail.io', 'yourmail.net', 'webservice.org', 'globalmail.com'
        ];

        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $username_length = mt_rand(5, 10);
        $username = '';

        for ($i = 0; $i < $username_length; $i++) {
            $username .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        $domain = $domains[array_rand($domains)];

        return $username . '@' . $domain;
    }

    public static function phoneNumberE164(): string {
        $country_codes = [
            '+1', '+7', '+27', '+31', '+32', '+33', '+34', '+352', '+39', '+44',
            '+46', '+47', '+48', '+49', '+55', '+61', '+64', '+81', '+86', '+91', '+972'
        ];

        $country_code = $country_codes[array_rand($country_codes)];
        $country_code_length = strlen($country_code);

        $max_number_length = 15 - $country_code_length;

        $number_length = mt_rand(8, $max_number_length); // Generate between 8 and max possible length

        $first_digit = mt_rand(2, 9);
        $number = $first_digit;

        for ($i = 1; $i < $number_length; $i++) {
            $number .= mt_rand(0, 9);
        }

        return $country_code . $number;
    }

    public static function time(): string {
        $hours = str_pad(mt_rand(0, 23), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);
        $seconds = str_pad(mt_rand(0, 59), 2, '0', STR_PAD_LEFT);

        return $hours . ':' . $minutes . ':' . $seconds;
    }

    public static function relativeUrl(): string {
        $depth = mt_rand(1, 5);

        $generateRandomString = function($length) {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            return $random_string;
        };

        $url_path = '';
        for ($i = 0; $i < $depth; $i++) {
            $url_path .= '/' . $generateRandomString(6);
        }

        return $url_path;
    }

    public static function url($protocol = null): string {
        $protocols = ['http', 'https', 'ldap', 'dns', 'ftp'];

        if(is_null($protocol)) {
            $protocol = $protocols[array_rand($protocols)];
        }

        $domain_length = mt_rand(3, 10);
        $path_depth = mt_rand(0, 5);

        // Helper function to generate a random string of given length
        $generateRandomString = function($length) {
            $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $random_string = '';
            for ($i = 0; $i < $length; $i++) {
                $random_string .= $characters[random_int(0, strlen($characters) - 1)];
            }
            return $random_string;
        };

        // Generate domain
        $domain = $generateRandomString($domain_length) . '.' . $generateRandomString(3); // e.g., 'example.com'

        // Generate path
        $path = '';
        for ($i = 0; $i < $path_depth; $i++) {
            $path .= '/' . $generateRandomString(6);
        }

        return $protocol . '://' . $domain . $path;
    }

    public static function urlTel(): string {
        return 'tel:' . self::phoneNumberE164();
    }

    public static function urlMailto(): string {
        return 'mailto:' . self::email();
    }

    public static function iban(): string {
        $account_number_lengths = [
            'DE' => 10,
            'GB' => 10,
            'FR' => 11,
            'ES' => 12,
            'IT' => 12,
            'NL' => 10,
            'BE' => 9
        ];

        $bank_code_lengths = [
            'DE' => 8,
            'GB' => 6,
            'FR' => 5,
            'ES' => 4,
            'IT' => 5,
            'NL' => 4,
            'BE' => 3
        ];

        $bank_codes = array_keys($bank_code_lengths);
        $country_code = $bank_codes[array_rand($bank_codes)];
        $bank_code_length = $bank_code_lengths[$country_code];

        $generateRandomNumericString = function($length) {
            $number = '';
            for ($i = 0; $i < $length; $i++) {
                $number .= mt_rand(0, 9);
            }
            return $number;
        };

        $bank_code = $generateRandomNumericString($bank_code_length);
        $account_number_length = $account_number_lengths[$country_code];
        $account_number = $generateRandomNumericString($account_number_length);

        $iban_base = $country_code . '00' . $bank_code . $account_number;

        $iban_numeric = '';
        foreach (str_split($iban_base) as $char) {
            if (ctype_alpha($char)) {
                $iban_numeric .= ord($char) - 55;
            } else {
                $iban_numeric .= $char;
            }
        }

        $mod97 = bcmod($iban_numeric, '97');
        $check_digits = str_pad(98 - $mod97, 2, '0', STR_PAD_LEFT);

        return $country_code . $check_digits . $bank_code . $account_number;
    }

    public static function ean13(): string {
        $ean12 = '';
        for ($i = 0; $i < 12; $i++) {
            $ean12 .= mt_rand(0, 9);
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$ean12[$i];
            if ($i % 2 === 0) {
                $sum += $digit;
            } else {
                $sum += $digit * 3;
            }
        }

        $check_digit = (10 - ($sum % 10)) % 10;

        return $ean12 . $check_digit;
    }

    public static function username(): string {
        $usernames = [
            'user', 'coolUser', 'jane-doe', 'johnnyBravo', 'theRealMike',
            'superstar', 'gameMaster', 'techGuru', 'quickSilver', 'happyCamper',
            'blueSky', 'codingWizard', 'magicMikey', 'fastTrack', 'misterX',
            'adventureSeeker', 'pixelPioneer', 'ninja-warrior', 'starGazer', 'drSmart',
            'boldExplorer', 'zenMaster', 'risingStar', 'rocket-rider', 'digitalNomad',
            'echoEcho', 'nightOwl', 'lightSpeed', 'trueBeliever', 'cyberHawk',
            'galacticHero', 'luckyCharm', 'urbanVibes', 'silentStorm', 'wildWanderer',
            'moonWalker', 'brightStar', 'vividDreamer', 'vortexVoyager', 'infiniteLoop',
            'horizonChaser', 'quickSilverFox', 'shadowKnight', 'dataMaster', 'epicQuest',
            'cosmicDancer', 'virtualVictor', 'alphaBravo', 'gammaRay', 'quantumLeap',
            'alphaWolf', 'digitalDynamo', 'codeNinja', 'retro-rider', 'futureFreak',
            'hyperLink', 'wizardKing', 'neonNinja', 'techTitan', 'starshipPilot',
            'legendaryHero', 'phantomShadow', 'urbanLegend', 'novaStar', 'daringDiva',
            'trailBlazer', 'cyberChampion', 'epicGamer', 'stellarScribe', 'stormChaser',
            'lunarExplorer', 'plasma-bolt', 'infinityEdge', 'quantumQuest', 'stellar-voyager'
        ];

        $number = mt_rand(0, 9999);

        return $usernames[array_rand($usernames)] . $number;
    }

    public static function firstname($lang = null): string {
        $map_lang_firstnames = [
            'en' => [
                'John', 'Jane', 'Michael', 'Emily', 'Robert', 'Jessica', 'David', 'Sarah',
                'James', 'Laura', 'Daniel', 'Sophia', 'Matthew', 'Olivia', 'Andrew', 'Isabella',
                'William', 'Mia', 'Joseph', 'Charlotte', 'Charles', 'Amelia', 'Thomas', 'Harper',
                'Christopher', 'Evelyn', 'Benjamin', 'Abigail', 'Samuel', 'Ella', 'Henry', 'Avery',
                'Lucas', 'Sofia', 'Jack', 'Grace', 'Jackson', 'Chloe', 'Ethan', 'Zoe', 'Alexander',
                'Lily', 'Ryan', 'Hannah', 'Nathan', 'Scarlett', 'Gabriel', 'Aria', 'Carter', 'Mila',
                'Isaac', 'Ella', 'Luke', 'Madison', 'Owen', 'Penelope', 'Caleb', 'Riley', 'Aiden',
                'Samantha', 'Dylan', 'Eleanor', 'Joshua', 'Layla', 'Mason', 'Nora', 'Logan', 'Lila',
                'Eli', 'Hazel', 'Cameron', 'Audrey', 'Sebastian', 'Ellie', 'Grayson', 'Stella',
                'Julian', 'Luna', 'Hudson', 'Lila', 'Wyatt', 'Nina', 'Mateo', 'Cora', 'Isaiah',
                'Vivian', 'Jordan', 'Katherine', 'Leo', 'Mackenzie', 'Harrison', 'Paige', 'Evan',
                'Alice', 'Jaxon', 'Eliana', 'Asher', 'Lydia', 'Leo', 'Julia', 'Miles', 'Caroline',
                'Jeremiah', 'Kylie', 'Jasper', 'Adeline', 'Roman', 'Piper', 'Ezekiel', 'Claire',
                'Xavier', 'Riley', 'Sawyer', 'Serenity', 'Kinsley', 'Maya', 'Zachary', 'Madeline',
                'Ariana', 'Aiden', 'Eliza', 'Avery', 'Liam', 'Sophie', 'Jaxon', 'Evangeline',
                'Daniel', 'Anna', 'Hudson', 'Natalie', 'Eli', 'Mia', 'Sebastian', 'Quinn',
                'Jameson', 'Everly', 'Santiago', 'Aurora', 'Roman', 'Naomi', 'Jackson', 'Ivy',
                'Finn', 'Riley', 'Oliver', 'Jade', 'Landon', 'Brianna', 'Gavin', 'Savannah',
                'Connor', 'Lily', 'Parker', 'Aubrey', 'Nolan', 'Violet', 'Bentley', 'Clara',
                'Levi', 'Ruby', 'Carson', 'Alyssa', 'Hunter', 'Faith', 'Eli', 'Zoey', 'Adrian',
                'Sienna', 'Cooper', 'Elise', 'Brody', 'Genesis', 'Grant', 'Harley', 'Tristan',
                'Eva', 'Easton', 'Sage', 'Tanner', 'Summer', 'Dominic', 'Maddie', 'Micah',
                'Tessa', 'Elias', 'Brooke', 'Elliot', 'Mallory', 'Theo', 'Delilah', 'Ryder',
                'Lana', 'Beckett', 'Reese', 'Axel', 'Anastasia', 'Malachi', 'Gemma', 'Bennett',
                'Talia', 'Brayden', 'Nadia', 'Silas', 'Camila', 'Jonah', 'Iris', 'Maxwell',
                'Isla', 'Tyler', 'Jasmine', 'Diego', 'Nova', 'Eric', 'Maren', 'Dean', 'Bianca',
                'Lincoln', 'Paisley', 'Hayden', 'Rose', 'Declan', 'Carmen', 'Oscar', 'Willa',
                'Griffin', 'Aspen', 'Ronan', 'Freya', 'Ezra', 'Willow', 'Kaden', 'Georgia'
            ],
            'fr' => [
                'Jean', 'Marie', 'Pierre', 'Sophie', 'Louis', 'Camille', 'Paul', 'Juliette',
                'Jacques', 'Chloé', 'Léon', 'Clara', 'Henri', 'Lucie', 'Thomas', 'Élodie',
                'Philippe', 'Manon', 'Michel', 'Léa', 'Nicolas', 'Amandine', 'François', 'Anaïs',
                'Antoine', 'Aurélie', 'Guillaume', 'Margaux', 'Étienne', 'Charlotte', 'Benoît',
                'Alice', 'Maxime', 'Julie', 'Hugo', 'Emilie', 'Théo', 'Isabelle', 'Vincent',
                'Valérie', 'Laurent', 'Cécile', 'Olivier', 'Maëlys', 'Damien', 'Catherine',
                'Adrien', 'Amélie', 'Georges', 'Émilie', 'Baptiste', 'Inès', 'Rémi', 'Océane',
                'Mathieu', 'Florian', 'Yves', 'Elsa', 'René', 'Jade', 'Claude', 'Clémentine',
                'André', 'Victoria', 'Gérard', 'Laure', 'Lucas', 'Sarah', 'Alain', 'Gabrielle',
                'Patrick', 'Madeleine', 'Simon', 'Louise', 'Raphaël', 'Soline', 'Arnaud', 'Léna',
                'Sébastien', 'Victoire', 'Gaspard', 'Maëlle', 'Charles', 'Rose', 'Mathis', 'Fanny',
                'Luc', 'Noémie', 'Christophe', 'Caroline', 'David', 'Jeanne', 'Emmanuel', 'Justine',
                'Xavier', 'Adèle', 'Pascal', 'Diane', 'Romain', 'Noé', 'Marc', 'Marion', 'Gaël',
                'Coralie', 'Cédric', 'Ariane','Françoise', 'Yvonne', 'Clément', 'Solange', 'Mathilde',
                'Bérénice', 'Thierry', 'Agnès', 'Pascaline', 'Alix', 'Roland', 'Brigitte', 'Sylvain',
                'Estelle', 'Fabrice', 'Lilian', 'Josiane', 'Éric', 'Serge', 'Cyril', 'Bernadette',
                'Guilhem', 'Axelle', 'Dominique', 'Ludovic', 'Véronique', 'Raymond', 'Sandrine',
                'Patrice', 'Colette', 'Basile', 'Félix', 'Jean-Marc', 'Maurice', 'Sylvie', 'Jacqueline',
                'Augustin', 'Gaston', 'Jean-Baptiste', 'Odile', 'Arlette', 'Marius', 'Christiane',
                'Fabien', 'Louison', 'Léonie', 'Yann', 'Noémie', 'Raphaëlle', 'Sébastienne', 'Florence',
                'Lucien', 'Jean-Luc', 'Fernand', 'Antoinette', 'Gisèle', 'Solène', 'Angèle', 'Edmond',
                'Céleste', 'Hélène', 'Violette'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_firstnames[$lang])) {
            $all_firstnames = array_merge(
                $map_lang_firstnames['en'],
                $map_lang_firstnames['fr']
            );

            return $all_firstnames[array_rand($all_firstnames)];
        }

        return $map_lang_firstnames[$lang][array_rand($map_lang_firstnames[$lang])];
    }

    public static function lastname($lang = null): string {
        $map_lang_lastnames = [
            'en' => [
                'Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson',
                'Moore', 'Taylor', 'Anderson', 'Thomas', 'Jackson', 'White', 'Harris', 'Martin',
                'Thompson', 'Garcia', 'Martinez', 'Robinson', 'Clark', 'Rodriguez', 'Lewis',
                'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Scott', 'Torres',
                'Nguyen', 'Hill', 'Adams', 'Baker', 'Nelson', 'Carter', 'Mitchell', 'Perez',
                'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards',
                'Collins', 'Stewart', 'Sanchez', 'Morris', 'Rogers', 'Reed', 'Cook', 'Morgan',
                'Bell', 'Murphy', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox', 'Howard',
                'Ward', 'Flores', 'Wood', 'James', 'Bennett', 'Gray', 'Mendoza', 'Cruz',
                'Hughes', 'Price', 'Myers', 'Long', 'Foster', 'Sanders', 'Ross', 'Morales',
                'Powell', 'Jenkins', 'Perry', 'Butler', 'Barnes', 'Fisher', 'Henderson',
                'Coleman', 'Simmons', 'Patterson', 'Jordan', 'Reynolds', 'Hamilton',
                'Graham', 'Kim', 'Gonzalez', 'Vasquez', 'Sullivan', 'Bryant', 'Alexander',
                'Russell', 'Griffin', 'Diaz', 'Hayes', 'Wells', 'Chavez', 'Burke', 'Wood',
                'Harrison', 'Gordon', 'Walters', 'McDonald', 'Murray', 'Ford', 'Hamilton',
                'Gibson', 'Ellis', 'Ramos', 'Fisher', 'George', 'Miller', 'Harris', 'James',
                'Stone', 'Richards', 'Hunter', 'Bennett', 'Perry', 'Matthews', 'Hughes',
                'Palmer', 'Burns', 'Floyd', 'Nguyen', 'Snyder', 'Bishop', 'Newman', 'Boone',
                'Dean', 'Carr', 'Cunningham', 'Sampson', 'Marshall', 'Barnett', 'Farrell',
                'Weaver', 'Wade', 'Bradley', 'Mason', 'Newton', 'Olson',
                'Hawkins', 'Chapman', 'Bowman', 'Lawrence', 'Glover', 'Barber', 'Grant', 'Wallace',
                'Keller', 'Webb', 'Spencer', 'Harvey', 'Brooks', 'Pearson', 'Francis', 'Burgess',
                'Graves', 'Lambert', 'Cross', 'Tucker', 'Fields', 'Reeves', 'Gibbs', 'Porter',
                'Daniels', 'Brady', 'Owen', 'Horton', 'McCarthy', 'Fletcher', 'Simon', 'Norris',
                'Clayton', 'Kemp', 'Fuller', 'Tyler', 'Pearce', 'Moss', 'Rowe', 'Hodges', 'Barker',
                'Hardy', 'Jennings', 'Gilbert', 'Payne', 'Webster', 'Neal', 'Sutton', 'Davidson',
                'Carlson', 'Morton', 'Kirk', 'Holland', 'Greer', 'Wheeler', 'Peters', 'Fordham'
            ],
            'fr' => [
                'Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy', 'Moreau',
                'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'David', 'Bertrand', 'Roux', 'Vincent', 'Fournier',
                'Morel', 'Girard', 'Andre', 'Lefevre', 'Mercier', 'Dupont', 'Lambert', 'Bonnet', 'Francois', 'Martinez',
                'Legrand', 'Garnier', 'Faure', 'Rousseau', 'Blanc', 'Guerin', 'Muller', 'Henry', 'Roussel', 'Nicolas',
                'Perrin', 'Morin', 'Mathieu', 'Clement', 'Gauthier', 'Dumont', 'Lopez', 'Fontaine', 'Chevalier',
                'Robin', 'Masson', 'Sanchez', 'Gerard', 'Nguyen', 'Boyer', 'Denis', 'Lemoine', 'Duval', 'Joly',
                'Gautier', 'Roger', 'Renaud', 'Gaillard', 'Hamond', 'Boucher', 'Carpentier', 'Menard', 'Marechal',
                'Charpentier', 'Dupuis', 'Leclerc', 'Poirier', 'Guillaume', 'Leconte', 'Benoit', 'Collet',
                'Perrot', 'Jacquet', 'Rey', 'Gilles', 'Herve', 'Charrier', 'Schmitt', 'Baron', 'Perret',
                'Leblanc', 'Verdier', 'Giraud', 'Marty', 'Lemoine', 'Poulain', 'Vallet', 'Renard',
                'Marion', 'Marchand', 'Chauvin', 'Langlois', 'Teixeira', 'Bellamy', 'Lemoigne', 'Bazin',
                'Da Silva', 'Delorme', 'Aubry', 'Ferreira', 'Chauvet', 'Delaunay', 'Joubert', 'Vidal',
                'Pires', 'Blondel', 'Noel', 'Collin', 'Lucas', 'Monnier', 'Breton', 'Lejeune', 'Prevost',
                'Allard', 'Pichon', 'Le Gall', 'Lavigne', 'Roy', 'Gros', 'Chartier', 'Briand', 'Maillet',
                'Lemois', 'Dufour', 'Boutin', 'Guichard', 'Vasseur', 'Hoarau', 'Lebrun', 'Giraudet',
                'Dubois', 'Maillard', 'Millet', 'Carre', 'Coste', 'Laborde', 'Bertin', 'Moulin',
                'Turpin', 'Deschamps', 'Barthelemy', 'Descamps', 'Riviere', 'Guilbert', 'Tanguy',
                'Duchamp', 'Pasquier', 'Gaudin', 'Vial', 'Letellier', 'Meunier', 'Bouchet', 'Hebert',
                'Gosselin', 'Le Roux', 'Renou', 'Guillon', 'Delattre', 'Lefranc', 'Peltier', 'Delacroix',
                'Labbe', 'Bellanger', 'Perrier', 'Chretien', 'Bouvet', 'Ferrand', 'Vallee', 'Boulanger',
                'Vautier', 'Morvan', 'Leclercq', 'Picard', 'Jourdain', 'Cornu', 'Bodin', 'Courtois',
                'Duhamel', 'Leveque', 'Leconte', 'Aubert', 'Delaire', 'Letourneau', 'Tessier', 'Barre',
                'Fleury', 'Mallet', 'Deniau', 'Royer', 'Rigal', 'Levy', 'Bouchard', 'Charron', 'Laroche'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_lastnames[$lang])) {
            $all_lastnames = array_merge(
                $map_lang_lastnames['en'],
                $map_lang_lastnames['fr']
            );

            return $all_lastnames[array_rand($all_lastnames)];
        }

        return $map_lang_lastnames[$lang][array_rand($map_lang_lastnames[$lang])];
    }

    public static function fullname($lang = null): string {
        return sprintf('%s %s', self::firstname($lang), self::lastname($lang));
    }

    public static function legalName($lang = null): string {
        $map_lang_legalNames = [
            'en' => [
                'Pinnacle Innovations Ltd.', 'GlobalTech Solutions LLC',
                'Blue Horizon Enterprises Inc.', 'Silverline Consulting Group',
                'Evergreen Holdings Co.', 'Brightway Capital Partners',
                'NextGen Software Corp.', 'Summit Legal Advisors LLP',
                'Aspire Marketing Associates', 'Crestview Financial Services',
                'Vanguard Media Networks', 'Oceanic Development Group',
                'Phoenix Engineering Systems', 'Cascade Resource Management',
                'Momentum Logistics Group', 'EagleRock Ventures LLC',
                'Fusion Health & Wellness Inc.', 'Broadway Research Institute',
                'Skyline Construction Co.', 'Peak Performance Consulting',
                'Heritage Legal Solutions LLP', 'Infinity Investment Trust',
                'Starlight Communications Inc.', 'Aurora Environmental Services',
                'Velocity Tech Innovators LLC', 'Redwood Analytics Solutions',
                'Everest Property Management', 'Sapphire Insurance Group Ltd.',
                'Nova Digital Media Inc.', 'Paramount Energy Solutions',
                'UrbanCore Construction Co.', 'TerraNova Agricultural Services',
                'Quantum Financial Consulting', 'Synergy Global Partners',
                'Horizon Security Solutions LLC', 'NorthStar Investments Group',
                'Zenith Technology Ventures', 'PrimeCare Medical Solutions',
                'Aurora Legal Advisory Group', 'Lakeside Manufacturing Co.',
                'Falcon Ridge Asset Management', 'Emerald Coast Logistics LLC',
                'Atlas Legal Services LLP', 'Titanium Energy Systems',
                'Genesis Marketing Innovations', 'Renaissance Capital Advisors',
                'TrueNorth Consulting Group', 'BluePeak Industries Inc.',
                'Omega Transportation Services', 'GoldenBridge Infrastructure Co.',
                'Sunrise Financial Holdings', 'Pioneer Technology Group LLC'
            ],
            'fr' => [
                'Solutions Pinnacle SARL', 'GlobalTech Innovations SAS',
                'Horizon Bleu Entreprises SA', 'Consulting Silverline Group',
                'Evergreen Holdings SARL', 'Capital Brightway Partners',
                'NextGen Software Corp.', 'Conseil Juridique Summit SCP',
                'Aspire Marketing Associés', 'Services Financiers Crestview',
                'Vanguard Médias Réseaux SARL', 'Groupe de Développement Océanique',
                'Phoenix Ingénierie Systèmes', 'Gestion des Ressources Cascade',
                'Momentum Logistique SARL', 'EagleRock Ventures SAS',
                'Fusion Santé & Bien-être Inc.', 'Institut de Recherche Broadway',
                'Construction Skyline SARL', 'Performance Consulting Peak',
                'Solutions Juridiques Héritage SCP', 'Infinity Investissement Trust',
                'Communications Starlight Inc.', 'Services Environnementaux Aurora',
                'Innovateurs Technologiques Velocity SAS', 'Alpha Conseil et Stratégie',
                'Groupe Nexus Immobilier', 'Aquitaine Développement Durable',
                'Astéria Informatique SARL', 'Helios Technologies SAS',
                'Artisanat Moderne & Tradition', 'Biotech Avancée France SA',
                'Espace Vert Solutions', 'Union des Commerçants Réunis',
                'Nouvelle Génération Énergies', 'Réseaux Mondial Média',
                'EcoSys Environnement SAS', 'Orion Énergie Renouvelable',
                'Alliance Pharmaceutique France', 'Cœur de Ville Projets Urbains',
                'Pôle d\'Innovation Numérique', 'Ciel Bleu Aviation SARL',
                'Altura Services Financiers', 'Vision Logistique Internationale',
                'Verdi Éco-Construction SARL', 'Genèse Création et Design',
                'Solstice Groupe Juridique', 'TechnoVentures France',
                'Esprit Nature & Bien-être SARL', 'Voies Nouvelles Mobilité SAS',
                'Groupe Horizon Santé SAS'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_legalNames[$lang])) {
            $all_legal_names = array_merge(
                $map_lang_legalNames['en'],
                $map_lang_legalNames['fr']
            );

            return $all_legal_names[array_rand($all_legal_names)];
        }

        return $map_lang_legalNames[$lang][array_rand($map_lang_legalNames[$lang])];
    }

    public static function addressStreet($lang = null): string {
        $number = mt_rand(1, 1200);

        $map_lang_streets = [
            'en' => [
                'High Street', 'Main Road', 'Church Lane', 'King Street', 'Victoria Avenue', 'Queen’s Road',
                'Park Lane', 'Elm Street', 'Bridge Road', 'Cedar Drive', 'Oxford Street', 'Market Square',
                'Mill Lane', 'Greenwich Way', 'Meadow Road', 'Kingfisher Close', 'Rosemary Avenue',
                'West End', 'Holly Road', 'Sunset Boulevard', 'Newcastle Street', 'Broadway', 'Pine Hill',
                'St. James’s Street', 'Bayview Road', 'Shakespeare Avenue', 'Cloverleaf Drive', 'Springfield Road',
                'Wellington Street', 'Harrison Close', 'Golden Grove', 'Elmwood Avenue', 'Rose Lane',
                'Abbey Road', 'Silver Street', 'Orchard Way', 'Granite Road', 'Oakfield Drive', 'Riverside Walk',
                'Broad Street', 'Main Avenue', 'River Lane', 'Hampton Road', 'Baker Street', 'Long Lane',
                'Park Avenue', 'Crescent Road', 'Hilltop Drive', 'Northgate Street', 'Cleveland Avenue',
                'St. John’s Road', 'Meadowbrook Lane', 'Silverwood Drive', 'Greenfield Road', 'Windsor Crescent',
                'Castle Street', 'Fairview Avenue', 'Ridgeway Road', 'Cumberland Street', 'Abbey Lane',
                'Kingsway', 'Dove Court', 'Hollingsworth Road', 'Starlight Avenue', 'Sunrise Lane',
                'Copperfield Road', 'Thornfield Drive', 'Holly Hill', 'Fairfax Street', 'Violet Lane',
                'Laurel Avenue', 'Tudor Street', 'Bramble Road', 'Claremont Drive', 'Woodland Avenue',
                'Glenwood Road', 'Beaumont Street', 'Ridgewood Avenue', 'Daisy Lane', 'Waverley Road',
                'King’s Crescent', 'Hawthorn Road', 'Elmwood Drive', 'Haven Street', 'Meadow Street'
            ],
            'fr' => [
                'Rue de la Paix', 'Avenue des Champs-Élysées', 'Boulevard Saint-Germain', 'Place de la Concorde',
                'Rue de Rivoli', 'Avenue Victor Hugo', 'Boulevard Haussmann', 'Rue du Faubourg Saint-Honoré',
                'Avenue de l\'Opéra', 'Rue Saint-Denis', 'Place Vendôme', 'Rue de la République',
                'Boulevard Voltaire', 'Rue Lafayette', 'Avenue de la Grande Armée', 'Rue du Bac',
                'Avenue Montaigne', 'Boulevard de Sébastopol', 'Place d’Italie', 'Rue de la Fontaine',
                'Rue des Rosiers', 'Rue de Vaugirard', 'Avenue de Paris', 'Boulevard de Strasbourg',
                'Place des Vosges', 'Rue Monge', 'Avenue Foch', 'Rue de la Garenne', 'Boulevard Saint-Michel',
                'Rue de la Chapelle', 'Place du Trocadéro', 'Rue de l’Université', 'Boulevard Diderot',
                'Rue des Écoles', 'Avenue de l\'Alma', 'Rue du Temple', 'Boulevard de la Madeleine',
                'Rue du Commerce', 'Avenue des Ternes', 'Boulevard de la Liberté', 'Place des Jardins',
                'Rue du Pont-Neuf', 'Avenue de la République', 'Boulevard de la Gare', 'Rue des Fleurs',
                'Place du Marché', 'Rue du Général Leclerc', 'Avenue des Écoles', 'Boulevard du Palais',
                'Rue des Lilas', 'Avenue de la Mairie', 'Boulevard des Capucines', 'Place des Armes',
                'Rue des Peupliers', 'Avenue du Président Wilson', 'Boulevard de l’Indépendance', 'Rue des Roses',
                'Place du Général de Gaulle', 'Rue des Pommiers', 'Avenue de la Victoire', 'Boulevard de la Mer',
                'Rue des Vergers', 'Avenue des Arts', 'Boulevard de la Côte', 'Place du Château', 'Rue de la Gare',
                'Rue de l’Église', 'Avenue de la Forêt', 'Boulevard du Parc', 'Place du Soleil', 'Rue des Vignes',
                'Rue de la Liberté', 'Avenue des Érables', 'Boulevard des Temples', 'Place de la République',
                'Rue des Champs', 'Avenue des Pyramides', 'Boulevard des Pruniers', 'Place de la Bastille'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_streets[$lang])) {
            $all_streets = array_merge(
                $map_lang_streets['en'],
                $map_lang_streets['fr']
            );

            return $all_streets[array_rand($all_streets)] . ' ' . $number;
        }

        return $map_lang_streets[$lang][array_rand($map_lang_streets[$lang])] . ' ' . $number;
    }

    public static function addressDispatch($lang = null): ?string {
        $apartmentNumber = rand(-50, 50);

        $apartment_name = $lang === 'fr' ? 'Bte' : 'Apartment';

        return $apartmentNumber > 0 ? "$apartment_name $apartmentNumber" : null;
    }

    public static function addressZip(): string {
        return mt_rand(1000, 9999);
    }

    public static function addressCity($lang = null): string {
        $map_lang_cities = [
            'en' => [
                'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
                'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose',
                'Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Edmonton',
                'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener',
                'London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool',
                'Edinburgh', 'Leeds', 'Sheffield', 'Bristol', 'Cardiff',
                'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide',
                'Gold Coast', 'Canberra', 'Hobart', 'Darwin', 'Newcastle',
                'Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt',
                'Stuttgart', 'Dusseldorf', 'Dortmund', 'Essen', 'Leipzig',
                'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
                'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille',
                'Rome', 'Milan', 'Naples', 'Turin', 'Palermo',
                'Genoa', 'Bologna', 'Florence', 'Catania', 'Venice',
                'Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza',
                'Malaga', 'Murcia', 'Palma', 'Las Palmas', 'Bilbao',
                'Brussels', 'Antwerp', 'Ghent', 'Bruges', 'Liege',
                'Namur', 'Ostend', 'Leuven', 'Hasselt', 'Mechelen',
                'Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven',
                'Groningen', 'Maastricht', 'Arnhem', 'Nijmegen', 'Haarlem',
                'Zurich', 'Geneva', 'Bern', 'Basel', 'Lausanne',
                'Lucerne', 'St. Moritz', 'Zug', 'Neuchatel', 'La Chaux-de-Fonds',
                'Tokyo', 'Osaka', 'Kyoto', 'Nagoya', 'Hiroshima',
                'Fukuoka', 'Kobe', 'Yokohama', 'Sapporo', 'Sendai',
                'Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu',
                'Hong Kong', 'Hangzhou', 'Nanjing', 'Wuhan', 'Xi\'an',
                'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad',
                'Chennai', 'Kolkata', 'Pune', 'Jaipur', 'Surat',
                'Sao Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Belo Horizonte',
                'Brasilia', 'Curitiba', 'Manaus', 'Recife', 'Porto Alegre',
                'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth',
                'Bloemfontein', 'East London', 'Polokwane', 'Nelspruit', 'Mbombela'
            ],
            'fr' => [
                'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
                'Philadelphie', 'San Antonio', 'San Diego', 'Dallas', 'San José',
                'Toronto', 'Vancouver', 'Montréal', 'Calgary', 'Edmonton',
                'Ottawa', 'Winnipeg', 'Québec', 'Hamilton', 'Kitchener',
                'Londres', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool',
                'Édimbourg', 'Leeds', 'Sheffield', 'Bristol', 'Cardiff',
                'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adélaïde',
                'Gold Coast', 'Canberra', 'Hobart', 'Darwin', 'Newcastle',
                'Berlin', 'Hambourg', 'Munich', 'Cologne', 'Francfort',
                'Stuttgart', 'Dusseldorf', 'Dortmund', 'Essen', 'Leipzig',
                'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
                'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille',
                'Rome', 'Milan', 'Naples', 'Turin', 'Palermo',
                'Gênes', 'Bologne', 'Florence', 'Catane', 'Venise',
                'Madrid', 'Barcelone', 'Valence', 'Séville', 'Saragosse',
                'Malaga', 'Murcie', 'Palma', 'Las Palmas', 'Bilbao',
                'Bruxelles', 'Anvers', 'Gand', 'Bruges', 'Liège',
                'Namur', 'Ostende', 'Leuven', 'Hasselt', 'Malines',
                'Amsterdam', 'Rotterdam', 'La Haye', 'Utrecht', 'Eindhoven',
                'Groningue', 'Maastricht', 'Arnhem', 'Nijmegen', 'Haarlem',
                'Zurich', 'Genève', 'Berne', 'Bâle', 'Lausanne',
                'Lucerne', 'Saint-Moritz', 'Zoug', 'Neuchâtel', 'La Chaux-de-Fonds',
                'Tokyo', 'Osaka', 'Kyoto', 'Nagoya', 'Hiroshima',
                'Fukuoka', 'Kobe', 'Yokohama', 'Sapporo', 'Sendai',
                'Pékin', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu',
                'Hong Kong', 'Hangzhou', 'Nankin', 'Wuhan', 'Xi’an',
                'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad',
                'Chennai', 'Kolkata', 'Pune', 'Jaipur', 'Surat',
                'Sao Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Belo Horizonte',
                'Brasilia', 'Curitiba', 'Manaus', 'Recife', 'Porto Alegre',
                'Johannesbourg', 'Le Cap', 'Durban', 'Prétoria', 'Port Elizabeth',
                'Bloemfontein', 'East London', 'Polokwane', 'Nelspruit', 'Mbombela'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_cities[$lang])) {
            $all_cities = array_merge(
                $map_lang_cities['en'],
                $map_lang_cities['fr']
            );

            return $all_cities[array_rand($all_cities)];
        }

        return $map_lang_cities[$lang][array_rand($map_lang_cities[$lang])];
    }

    public static function addressState(): string {
        $map_lang_states = [
            // Belgium
            'Antwerp', 'East Flanders', 'West Flanders', 'Flemish Brabant', 'Brussels-Capital Region', 'Hainaut',
            'Liège', 'Luxembourg', 'Namur', 'Walloon Brabant', 'Brussels-Capital',

            //France
            'Île-de-France', 'Provence-Alpes-Côte d\'Azur', 'Auvergne-Rhône-Alpes', 'Nouvelle-Aquitaine', 'Occitanie',
            'Hauts-de-France', 'Normandy', 'Brittany', 'Grand Est', 'Bourgogne-Franche-Comté', 'Pays de la Loire',
            'Centre-Val de Loire', 'Alsace', 'Corsica', 'Mayotte',

            // Netherlands
            'Groningen', 'Friesland', 'Drenthe', 'Overijssel', 'Flevoland', 'Gelderland', 'Utrecht',
            'North Holland', 'South Holland', 'Zeeland', 'North Brabant', 'Limburg',

            // England
            'Northumberland', 'County Durham', 'Tyne and Wear', 'Cheshire', 'Greater Manchester', 'Lancashire',
            'Merseyside', 'East Riding of Yorkshire', 'North Yorkshire', 'South Yorkshire', 'West Yorkshire',
            'Derbyshire', 'Leicestershire', 'Lincolnshire', 'Northamptonshire', 'Nottinghamshire', 'Herefordshire',
            'Shropshire', 'Staffordshire', 'Warwickshire', 'Worcestershire', 'Bedfordshire', 'Cambridgeshire',
            'Essex', 'Hertfordshire', 'Norfolk', 'Suffolk', 'Greater London', 'Berkshire', 'Buckinghamshire',
            'East Sussex', 'Hampshire', 'Kent', 'Oxfordshire', 'Surrey', 'West Sussex', 'Cornwall', 'Devon',
            'Dorset', 'Gloucestershire', 'Somerset', 'Wiltshire',

            // Germany
            'Baden-Württemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hesse', 'Lower Saxony',
            'North Rhine-Westphalia', 'Rhineland-Palatinate', 'Saarland', 'Saxony', 'Saxony-Anhalt',
            'Schleswig-Holstein', 'Thuringia', 'Mecklenburg-Vorpommern'
        ];

        return $map_lang_states[array_rand($map_lang_states)];
    }

    public static function addressCountry($lang = null): string {
        $map_lang_countries = [
            'en' => [
                'Afghanistan', 'Albania', 'Algeria', 'Andorra', 'Angola',
                'Antigua and Barbuda', 'Argentina', 'Armenia', 'Australia', 'Austria',
                'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados',
                'Belarus', 'Belgium', 'Belize', 'Benin', 'Bhutan',
                'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Brazil', 'Brunei',
                'Bulgaria', 'Burkina Faso', 'Burundi', 'Cabo Verde', 'Cambodia',
                'Cameroon', 'Canada', 'Central African Republic', 'Chad', 'Chile',
                'China', 'Colombia', 'Comoros', 'Congo, Democratic Republic of the', 'Congo, Republic of the',
                'Costa Rica', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic',
                'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic',
                'East Timor', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea',
                'Eritrea', 'Estonia', 'Eswatini', 'Ethiopia', 'Fiji',
                'Finland', 'France', 'Gabon', 'Gambia', 'Georgia',
                'Germany', 'Ghana', 'Greece', 'Grenada', 'Guatemala',
                'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Honduras',
                'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran',
                'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica',
                'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati',
                'Korea, North', 'Korea, South', 'Kosovo', 'Kuwait', 'Kyrgyzstan',
                'Laos', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia',
                'Libya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Madagascar',
                'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta',
                'Marshall Islands', 'Mauritania', 'Mauritius', 'Mexico', 'Micronesia',
                'Moldova', 'Monaco', 'Mongolia', 'Montenegro', 'Morocco',
                'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal',
                'Netherlands', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria',
                'North Macedonia', 'Norway', 'Oman', 'Pakistan', 'Palau',
                'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines',
                'Poland', 'Portugal', 'Qatar', 'Romania', 'Russia',
                'Rwanda', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines', 'Samoa',
                'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia',
                'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia',
                'Solomon Islands', 'Somalia', 'South Africa', 'South Sudan', 'Spain',
                'Sri Lanka', 'Sudan', 'Suriname', 'Sweden', 'Switzerland',
                'Syria', 'Taiwan', 'Tajikistan', 'Tanzania', 'Thailand',
                'Timor-Leste', 'Togo', 'Tonga', 'Trinidad and Tobago', 'Tunisia',
                'Turkey', 'Turkmenistan', 'Tuvalu', 'Uganda', 'Ukraine',
                'United Arab Emirates', 'United Kingdom', 'United States', 'Uruguay', 'Uzbekistan',
                'Vanuatu', 'Vatican City', 'Venezuela', 'Vietnam', 'Yemen',
                'Zambia', 'Zimbabwe'
            ],
            'fr' => [
                'Afghanistan', 'Albanie', 'Algérie', 'Andorre', 'Angola',
                'Antigua-et-Barbuda', 'Argentine', 'Arménie', 'Australie', 'Autriche',
                'Azerbaïdjan', 'Bahamas', 'Bahreïn', 'Bangladesh', 'Barbade',
                'Bélarus', 'Belgique', 'Belize', 'Bénin', 'Bhoutan',
                'Bolivie', 'Bosnie-Herzégovine', 'Botswana', 'Brésil', 'Brunei',
                'Bulgarie', 'Burkina Faso', 'Burundi', 'Cap-Vert', 'Cambodge',
                'Cameroun', 'Canada', 'République Centrafricaine', 'Tchad', 'Chili',
                'Chine', 'Colombie', 'Comores', 'République Démocratique du Congo', 'République du Congo',
                'Côte d\'Ivoire', 'Costa Rica', 'Croatie', 'Cuba', 'Chypre',
                'République tchèque', 'Danemark', 'Djibouti', 'Dominique', 'République Dominicaine',
                'Timor oriental', 'Équateur', 'Égypte', 'El Salvador', 'Guinée équatoriale',
                'Érythrée', 'Estonie', 'Eswatini', 'Éthiopie', 'Fidji',
                'Finlande', 'France', 'Gabon', 'Gambie', 'Géorgie',
                'Allemagne', 'Ghana', 'Grèce', 'Grenade', 'Guatemala',
                'Guinée', 'Guinée-Bissau', 'Guyane', 'Haïti', 'Honduras',
                'Hongrie', 'Islande', 'Inde', 'Indonésie', 'Iran',
                'Irak', 'Irlande', 'Israël', 'Italie', 'Jamaïque',
                'Japon', 'Jordanie', 'Kazakhstan', 'Kenya', 'Kiribati',
                'Corée du Nord', 'Corée du Sud', 'Kosovo', 'Koweït', 'Kyrgyzstan',
                'Laos', 'Lettonie', 'Liban', 'Lesotho', 'Liberia',
                'Libye', 'Liechtenstein', 'Lituanie', 'Luxembourg', 'Madagascar',
                'Malawi', 'Malaisie', 'Maldives', 'Mali', 'Malte',
                'Îles Marshall', 'Mauritanie', 'Maurice', 'Mexique', 'Micronésie',
                'Moldova', 'Monaco', 'Mongolie', 'Monténégro', 'Maroc',
                'Mozambique', 'Myanmar', 'Namibie', 'Nauru', 'Népal',
                'Pays-Bas', 'Nouvelle-Zélande', 'Nicaragua', 'Niger', 'Nigeria',
                'Macédoine du Nord', 'Norvège', 'Oman', 'Pakistan', 'Palaos',
                'Panama', 'Papouasie-Nouvelle-Guinée', 'Paraguay', 'Pérou', 'Philippines',
                'Pologne', 'Portugal', 'Qatar', 'Roumanie', 'Russie',
                'Rwanda', 'Saint-Kitts-et-Nevis', 'Sainte-Lucie', 'Saint-Vincent-et-les-Grenadines', 'Samoa',
                'Saint-Marin', 'Sao Tomé-et-Principe', 'Arabie Saoudite', 'Sénégal', 'Serbie',
                'Seychelles', 'Sierra Leone', 'Singapour', 'Slovaquie', 'Slovénie',
                'Îles Salomon', 'Somalie', 'Afrique du Sud', 'Soudan du Sud', 'Espagne',
                'Sri Lanka', 'Soudan', 'Suriname', 'Suède', 'Suisse',
                'Syrie', 'Taïwan', 'Tadjikistan', 'Tanzanie', 'Thaïlande',
                'Timor-Leste', 'Togo', 'Tonga', 'Trinité-et-Tobago', 'Tunisie',
                'Turquie', 'Turkménistan', 'Tuvalu', 'Ouganda', 'Ukraine',
                'Émirats Arabes Unis', 'Royaume-Uni', 'États-Unis', 'Uruguay', 'Ouzbékistan',
                'Vanuatu', 'Vatican', 'Venezuela', 'Vietnam', 'Yémen',
                'Zambie', 'Zimbabwe'
            ]
        ];

        if(is_null($lang) || !isset($map_lang_countries[$lang])) {
            $all_countries = array_merge(
                $map_lang_countries['en'],
                $map_lang_countries['fr']
            );

            return $all_countries[array_rand($all_countries)];
        }

        return $map_lang_countries[$lang][array_rand($map_lang_countries[$lang])];
    }

    public static function address($lang = null): string {
        return sprintf(
            '%s, %s %s, %s',
            self::addressStreet($lang),
            self::addressZip(),
            self::addressCity($lang),
            self::addressCountry($lang)
        );
    }

}
