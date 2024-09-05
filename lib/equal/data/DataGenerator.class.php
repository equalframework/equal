<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\data;

class DataGenerator {

    public static function plainText($max_length = 255): string {
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

        $random_length = mt_rand(0, $max_length);
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

    public static function integer(int $length): int {
        $min = (pow(10, $length) - 1) * -1;
        $max = pow(10, $length) - 1;

        return mt_rand($min, $max);
    }

    public static function realNumber(int $precision, int $scale): float {
        $max_int_part = pow(10, $precision) - 1;
        $min_int_part = -$max_int_part;

        $int_part = mt_rand($min_int_part, $max_int_part);

        $fractional_part = mt_rand(0, pow(10, $scale) - 1) / pow(10, $scale);

        $random_float = $int_part + $fractional_part;

        return round($random_float, $scale);
    }

    public static function hexadecimal(int $length): string {
        $num_bytes = ceil($length / 2);
        $random_bytes = random_bytes($num_bytes);
        $hexadecimal_string = bin2hex($random_bytes);

        return substr($hexadecimal_string, 0, $length);
    }

    public static function email(): string {
        $domains = ['example.com', 'test.com', 'demo.com', 'sample.org', 'mywebsite.net'];

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
            '+1', '+7', '+27', '+31', '+32', '+33', '+34', '+352', '+39', '+44', '+46',
            '+47', '+48', '+49', '+55', '+61', '+64', '+81', '+86', '+90', '+91', '+972'
        ];

        $country_code = $country_codes[array_rand($country_codes)];

        $number_length = 15 - strlen($country_code);
        $number = '';
        for ($i = 0; $i < $number_length; $i++) {
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

    public static function url(): string {
        $protocols = ['http', 'https', 'ldap', 'dns', 'ftp'];
        $protocol = $protocols[array_rand($protocols)];

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
        $generateRandomNumber = function($length) {
            $number = '';
            for ($i = 0; $i < $length; $i++) {
                $number .= mt_rand(0, 9);
            }
            return $number;
        };

        $phoneNumber = $generateRandomNumber(10);

        return 'tel:' . '+32' . $phoneNumber;
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
            'user', 'coolUser', 'jane_doe', 'johnnyBravo', 'theRealMike',
            'superstar', 'gameMaster', 'techGuru', 'quickSilver', 'happyCamper',
            'blueSky', 'codingWizard', 'magicMikey', 'fastTrack', 'misterX',
            'adventureSeeker', 'pixelPioneer', 'ninjaWarrior', 'starGazer', 'drSmart',
            'boldExplorer', 'zenMaster', 'risingStar', 'rocketRider', 'digitalNomad',
            'echoEcho', 'nightOwl', 'lightSpeed', 'trueBeliever', 'cyberHawk',
            'galacticHero', 'luckyCharm', 'urbanVibes', 'silentStorm', 'wildWanderer',
            'moonWalker', 'brightStar', 'vividDreamer', 'vortexVoyager', 'infiniteLoop',
            'horizonChaser', 'quickSilverFox', 'shadowKnight', 'dataMaster', 'epicQuest',
            'cosmicDancer', 'virtualVictor', 'alphaBravo', 'gammaRay', 'quantumLeap',
            'alphaWolf', 'digitalDynamo', 'codeNinja', 'retroRider', 'futureFreak',
            'hyperLink', 'wizardKing', 'neonNinja', 'techTitan', 'starshipPilot',
            'legendaryHero', 'phantomShadow', 'urbanLegend', 'novaStar', 'daringDiva',
            'trailBlazer', 'cyberChampion', 'epicGamer', 'stellarScribe', 'stormChaser',
            'lunarExplorer', 'plasmaBolt', 'infinityEdge', 'quantumQuest', 'stellarVoyager'
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

        if(is_null($lang) || !isset($map_lang_firstnames[$lang])) {
            $all_lastnames = array_merge(
                $map_lang_lastnames['en'],
                $map_lang_lastnames['fr']
            );

            return $all_lastnames[array_rand($all_lastnames)];
        }

        return $map_lang_lastnames[$lang][array_rand($map_lang_lastnames[$lang])];
    }

    public static function fullname(): string {
        return sprintf('%s %s', self::firstname(), self::lastname());
    }

    public static function addressStreet(): string {
        $number = mt_rand(1, 1200);

        $streets = [
            'Red Street', 'Blue Avenue', 'Green Lane', 'Yellow Road',
            'Orange Boulevard', 'Purple Drive', 'Pink Place', 'Brown Terrace',
            'Gray Court', 'White Crescent', 'Black Alley', 'Silver Way',
            'Gold Street', 'Copper Crescent', 'Bronze Drive', 'Platinum Road',
            'Emerald Lane', 'Ruby Street', 'Sapphire Avenue', 'Topaz Boulevard',
            'Diamond Drive', 'Jade Place', 'Onyx Terrace', 'Quartz Way',
            'Amethyst Avenue', 'Turquoise Road', 'Opal Lane', 'Amber Street',
            'Lime Boulevard', 'Violet Drive', 'Indigo Crescent', 'Teal Place',
            'Cyan Court', 'Magenta Terrace', 'Coral Way', 'Lavender Road',
            'Cherry Lane', 'Rose Avenue', 'Marigold Street', 'Daisy Boulevard',
            'Sunflower Drive', 'Iris Place', 'Lily Court', 'Poppy Way',
            'Hibiscus Terrace', 'Gardenia Road', 'Holly Lane', 'Tulip Avenue',
            'Azalea Boulevard', 'Dandelion Drive', 'Aster Street', 'Cosmos Place',
            'Bluebell Road ', 'Hyacinth Court', 'Buttercup Avenue', 'Foxglove Lane'
        ];

        return $streets[array_rand($streets)] . ' ' . $number;
    }

    public static function addressZip(): string {
        return str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function addressCity(): string {
        $cities = [
            // United States
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix',
            'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose',

            // Canada
            'Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Edmonton',
            'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener',

            // United Kingdom
            'London', 'Birmingham', 'Manchester', 'Glasgow', 'Liverpool',
            'Edinburgh', 'Leeds', 'Sheffield', 'Bristol', 'Cardiff',

            // Australia
            'Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide',
            'Gold Coast', 'Canberra', 'Hobart', 'Darwin', 'Newcastle',

            // Germany
            'Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt',
            'Stuttgart', 'Dusseldorf', 'Dortmund', 'Essen', 'Leipzig',

            // France
            'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice',
            'Nantes', 'Montpellier', 'Strasbourg', 'Bordeaux', 'Lille',

            // Italy
            'Rome', 'Milan', 'Naples', 'Turin', 'Palermo',
            'Genoa', 'Bologna', 'Florence', 'Catania', 'Venice',

            // Spain
            'Madrid', 'Barcelona', 'Valencia', 'Seville', 'Zaragoza',
            'Malaga', 'Murcia', 'Palma', 'Las Palmas', 'Bilbao',

            // Belgium
            'Brussels', 'Antwerp', 'Ghent', 'Bruges', 'Liege',
            'Namur', 'Ostend', 'Leuven', 'Hasselt', 'Mechelen',

            // Netherlands
            'Amsterdam', 'Rotterdam', 'The Hague', 'Utrecht', 'Eindhoven',
            'Groningen', 'Maastricht', 'Arnhem', 'Nijmegen', 'Haarlem',

            // Switzerland
            'Zurich', 'Geneva', 'Bern', 'Basel', 'Lausanne',
            'Lucerne', 'St. Moritz', 'Zug', 'Neuchatel', 'La Chaux-de-Fonds',

            // Japan
            'Tokyo', 'Osaka', 'Kyoto', 'Nagoya', 'Hiroshima',
            'Fukuoka', 'Kobe', 'Yokohama', 'Sapporo', 'Sendai',

            // China
            'Beijing', 'Shanghai', 'Guangzhou', 'Shenzhen', 'Chengdu',
            'Hong Kong', 'Hangzhou', 'Nanjing', 'Wuhan', 'Xi\'an',

            // India
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad',
            'Chennai', 'Kolkata', 'Pune', 'Jaipur', 'Surat',

            // Brazil
            'Sao Paulo', 'Rio de Janeiro', 'Salvador', 'Fortaleza', 'Belo Horizonte',
            'Brasilia', 'Curitiba', 'Manaus', 'Recife', 'Porto Alegre',

            // South Africa
            'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth',
            'Bloemfontein', 'East London', 'Polokwane', 'Nelspruit', 'Mbombela'
        ];

        return $cities[array_rand($cities)];
    }

    public static function addressCountry(): string {
        $countries = [
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
        ];

        return $countries[array_rand($countries)];
    }

    public static function address(): string {
        return sprintf(
            '%s, %s %s, %s',
            self::addressStreet(),
            self::addressZip(),
            self::addressCity(),
            self::addressCountry()
        );
    }

}
