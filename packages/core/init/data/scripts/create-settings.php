<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;

$import_data = static function(string $entity, array $localized_data): void {
    foreach($localized_data as $lang => $data) {
        eQual::run('do', 'core_model_import', [
            'entity'    => $entity,
            'data'      => $data,
            'lang'      => $lang
        ]);
    }
};

$rows_from_translations = static function(array $translations): array {
    $rows = [];
    foreach($translations as $id => $fields) {
        $rows[] = ['id' => $id] + $fields;
    }
    return $rows;
};

$import_data('core\setting\SettingSection', [
    'en' => [
        ['id' => 1,  'code' => 'locale',       'name' => 'Locale',       'description' => 'Regional settings'],
        ['id' => 2,  'code' => 'main',         'name' => 'Main',         'description' => 'Deprecated - use `organization`'],
        ['id' => 3,  'code' => 'security',     'name' => 'Security',     'description' => 'Security settings'],
        ['id' => 4,  'code' => 'default',      'name' => 'Default',      'description' => 'Default values'],
        ['id' => 5,  'code' => 'accounting',   'name' => 'Accounting',   'description' => 'Accounting (not only financial)'],
        ['id' => 11, 'code' => 'analytics',    'name' => 'Analytics',    'description' => 'Reporting & Analytics'],
        ['id' => 12, 'code' => 'features',     'name' => 'Features',     'description' => 'Customization & UI'],
        ['id' => 13, 'code' => 'storage',      'name' => 'Storage',      'description' => 'Storage & Data'],
        ['id' => 14, 'code' => 'integration',  'name' => 'Integration',  'description' => 'Integrations & Connectors'],
        ['id' => 15, 'code' => 'system',       'name' => 'System',       'description' => 'Technical & Maintenance'],
        ['id' => 16, 'code' => 'workflow',     'name' => 'Workflow',     'description' => 'Business Logic & Processes'],
        ['id' => 17, 'code' => 'schedule',     'name' => 'Schedule',     'description' => 'Scheduling & Time Config'],
        ['id' => 18, 'code' => 'organization', 'name' => 'Organization', 'description' => 'Structure & Organization']
    ],
    'fr' => [
        ['id' => 1,  'name' => 'Locale',          'description' => 'Paramètres régionaux'],
        ['id' => 2,  'name' => 'Général',         'description' => 'Paramètres généraux et de formats'],
        ['id' => 3,  'name' => 'Sécurité',        'description' => 'Paramètres de sécurité'],
        ['id' => 4,  'name' => 'Défaut',          'description' => 'Valeurs d\'assignation par défaut.'],
        ['id' => 5,  'name' => 'Comptabilité',    'description' => 'Comptabilité et suivi des flux'],
        ['id' => 11, 'name' => 'Analyse',         'description' => 'Statistiques, rapports et indicateurs'],
        ['id' => 12, 'name' => 'Fonctionnalités', 'description' => 'Personnalisation, UI et libellés'],
        ['id' => 13, 'name' => 'Stockage',        'description' => 'Stockage, fichiers et quotas'],
        ['id' => 14, 'name' => 'Intégration',     'description' => 'Connecteurs, API et interconnexions'],
        ['id' => 15, 'name' => 'Système',         'description' => 'Paramètres techniques et maintenance'],
        ['id' => 16, 'name' => 'Workflow',        'description' => 'Statuts, transitions et automatisations'],
        ['id' => 17, 'name' => 'Planification',   'description' => 'Horaires, calendriers et tâches planifiées'],
        ['id' => 18, 'name' => 'Organisation',    'description' => 'Structure, départements et périodes internes']
    ]
]);

$settings_en = [
    ['id' => 1, 'package' => 'core', 'code' => 'numbers.thousands_separator',       'title' => 'Thousands separator',                         'form_control' => 'select', 'section_id' => 1, 'description' => 'Character to use as thousands separator',                                      'help' => "Thousands separator char (e.g. '.', ',', ' ').",                                                                                       'type' => 'string'],
    ['id' => 2, 'package' => 'core', 'code' => 'currency.symbol_position',          'title' => 'Currency symbol position',                     'form_control' => 'select', 'section_id' => 1, 'description' => 'Position of the currency symbol relative to its value.',                         'help' => "Position of the currency symbol relative to its value ('before', 'after', 'decimal_separator').",                                      'type' => 'string'],
    ['id' => 3, 'package' => 'core', 'code' => 'currency.decimal_precision',        'title' => 'Number of decimal digits (Price)',             'form_control' => 'select', 'section_id' => 1, 'description' => 'Number of decimal digits for prices.',                                          'help' => "Number of decimal digits to store for fields of usage 'Price'.",                                                                        'type' => 'integer'],
    ['id' => 4, 'package' => 'core', 'code' => 'numbers.decimal_separator',         'title' => 'Decimal separator',                            'form_control' => 'select', 'section_id' => 1, 'description' => "Decimal separator (e.g. '.', ',')",                                            'help' => "Character to use for separating integer and decimal parts of floating numbers (e.g. '.', ',').",                                       'type' => 'string'],
    ['id' => 5, 'package' => 'core', 'code' => 'numbers.decimal_precision',         'title' => 'Number of decimal digits',                     'form_control' => 'select', 'section_id' => 1, 'description' => 'Number of decimal digits',                                                     'help' => "Number of decimal digits to store for fields of type 'float'.",                                                                         'type' => 'integer'],
    ['id' => 6, 'package' => 'core', 'code' => 'date_format',                       'title' => 'Date format',                                  'form_control' => 'select', 'section_id' => 1, 'description' => 'Date format',                                                                  'help' => 'Format to use for displaying dates.',                                                                                                   'type' => 'string'],
    ['id' => 7, 'package' => 'core', 'code' => 'time_format',                       'title' => 'Time format',                                  'form_control' => 'select', 'section_id' => 1, 'description' => 'Format for times.',                                                           'help' => 'Format to use for displaying times and time parts of dates.',                                                                           'type' => 'string'],
    ['id' => 8, 'package' => 'core', 'code' => 'company.id',                        'title' => 'Company',                                      'form_control' => 'select', 'section_id' => 2, 'description' => 'Main company of installation.',                                                'help' => 'Identifier of the main company of the current installation.',                                                                           'type' => 'integer'],
    ['id' => 9, 'package' => 'core', 'code' => 'formats.paper',                     'title' => 'Paper format',                                 'form_control' => 'select', 'section_id' => 2, 'description' => 'Default size for paper documents',                                             'help' => "Default size for paper documents (e.g. 'A4', 'legal_US', 'letter_US').",                                                               'type' => 'string'],
    ['id' => 10, 'package' => 'core', 'code' => 'account_creation',                  'title' => 'Account creation',                             'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow accounts creation',                                                     'help' => 'Allow visitors to create a user account.',                                                                                             'type' => 'boolean'],
    ['id' => 11, 'package' => 'core', 'code' => 'import',                            'title' => 'Import',                                       'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow import',                                                                'help' => 'Allow users to import data from files (CSV/XLS/XLSX/ODS).',                                                                            'type' => 'boolean'],
    ['id' => 12, 'package' => 'core', 'code' => 'export',                            'title' => 'Export',                                       'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow export',                                                                'help' => 'Allow users to export data by downloading files (CSV/XLS/PDF).',                                                                       'type' => 'boolean'],
    ['id' => 13, 'package' => 'core', 'code' => 'currency',                          'title' => 'Currency',                                     'form_control' => 'select', 'section_id' => 1, 'description' => 'Prices currency',                                                             'help' => "Currency to use for fields of usage 'Price' (ISO 4217).",                                                                              'type' => 'string'],
    ['id' => 14, 'package' => 'core', 'code' => 'length',                            'title' => 'Length',                                       'form_control' => 'select', 'section_id' => 1, 'description' => 'Length unit',                                                                 'help' => 'Default unit for length measures.',                                                                                                    'type' => 'string'],
    ['id' => 15, 'package' => 'core', 'code' => 'weight',                            'title' => 'Weight',                                       'form_control' => 'select', 'section_id' => 1, 'description' => 'Weight unit',                                                                 'help' => "Default unit for weight measures (e.g. 'kg' kilogram, 'lb' pound).",                                                                   'type' => 'string'],
    ['id' => 16, 'package' => 'core', 'code' => 'volume',                            'title' => 'Volume',                                       'form_control' => 'select', 'section_id' => 1, 'description' => 'Volume unit',                                                                 'help' => "Default unit for volume measures (e.g. 'm3' cubic meter, 'ft3' cubic foot).",                                                         'type' => 'string'],
    ['id' => 17, 'package' => 'core', 'code' => 'surface',                           'title' => 'Surface',                                      'form_control' => 'select', 'section_id' => 1, 'description' => 'Surface unit',                                                                'help' => "Default unit for surface measures (e.g. 'm2' square meter, 'ft2' square foot).",                                                       'type' => 'string'],
    ['id' => 18, 'package' => 'core', 'code' => 'time_zone',                         'title' => 'Time zone',                                    'form_control' => 'select', 'section_id' => 1, 'description' => 'Time zone on which dates are based.',                                         'help' => 'Default timezone to be used at application level.',                                                                                     'type' => 'string'],
    ['id' => 19, 'package' => 'core', 'code' => 'passkey_creation',                  'title' => 'Propose passkey creation',                     'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Propose passkey creation after login using password.',                        'help' => "Only proposes if the user hasn't any passkey yet.",                                                                                    'type' => 'boolean'],
    ['id' => 20, 'package' => 'core', 'code' => 'passkey_rp_id',                     'title' => 'Passkey relying party id',                     'form_control' => 'input',  'section_id' => 3, 'description' => 'The domain or unique identifier for the relying party.',                      'help' => 'Enter the domain or identifier used by the relying party. This is typically a domain name (e.g. example.com).',                         'type' => 'string'],
    ['id' => 21, 'package' => 'core', 'code' => 'passkey_rp_name',                   'title' => 'Passkey relying party name',                   'form_control' => 'input',  'section_id' => 3, 'description' => 'The name of the relying party.',                                             'help' => 'Enter the full name of the relying party. This is usually the name of the website or application (e.g., Example Inc.).',                'type' => 'string'],
    ['id' => 22, 'package' => 'core', 'code' => 'passkey_format_android-key',        'title' => 'Passkey format android-key',                   'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for Android Key passkey format.',                    'help' => 'Toggle this option if you want to allow passkeys to be verified using Android Key attestation.',                                       'type' => 'boolean'],
    ['id' => 23, 'package' => 'core', 'code' => 'passkey_format_android-safetynet',  'title' => 'Passkey format android-safetynet',             'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for Android SafetyNet passkey format.',              'help' => 'Toggle this option to allow passkeys verified using Android SafetyNet attestation.',                                                   'type' => 'boolean'],
    ['id' => 24, 'package' => 'core', 'code' => 'passkey_format_apple',              'title' => 'Passkey format apple',                         'form_control' => 'toggle', 'section_id' => 3, 'description' => "Enable or disable support for Apple's passkey format.",                       'help' => 'Toggle this option if you want to allow passkeys verified through Apple attestation.',                                                 'type' => 'boolean'],
    ['id' => 25, 'package' => 'core', 'code' => 'passkey_format_fido-u2f',           'title' => 'Passkey format fido-u2f',                      'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for FIDO U2F passkey format.',                       'help' => 'Toggle this option to allow passkeys verified using the FIDO U2F standard.',                                                           'type' => 'boolean'],
    ['id' => 26, 'package' => 'core', 'code' => 'passkey_format_none',               'title' => 'Passkey format none',                          'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for passkeys without attestation format.',           'help' => 'Toggle this option to allow passkeys that do not require formal attestation.',                                                         'type' => 'boolean'],
    ['id' => 27, 'package' => 'core', 'code' => 'passkey_format_packed',             'title' => 'Passkey format packed',                        'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for packed passkey format.',                        'help' => 'Toggle this option to allow passkeys using the packed format for attestation.',                                                        'type' => 'boolean'],
    ['id' => 28, 'package' => 'core', 'code' => 'passkey_format_tpm',                'title' => 'Passkey format tpm',                           'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable support for TPM (Trusted Platform Module) passkey format.',  'help' => 'Toggle this option to allow passkeys verified through TPM attestation.',                                                               'type' => 'boolean'],
    ['id' => 29, 'package' => 'core', 'code' => 'passkey_user_verification',         'title' => 'Passkey user verification',                    'form_control' => 'select', 'section_id' => 3, 'description' => 'Select the level of user verification required during passkey authentication.', 'help' => "Options: 'required' forces user verification (e.g., via biometrics or PIN), 'preferred' uses it if available, and 'discouraged' skips user verification.", 'type' => 'string'],
    ['id' => 30, 'package' => 'core', 'code' => 'passkey_cross_platform',            'title' => 'Passkey cross platform',                       'form_control' => 'select', 'section_id' => 3, 'description' => 'Choose the usage mode for passkeys across platforms.',                        'help' => "Options: 'all' for all types of passkeys, 'only cross-platform' for passkeys stored on external connected devices, or 'only platform' for passkeys limited to the device itself.", 'type' => 'string'],
    ['id' => 31, 'package' => 'core', 'code' => 'passkey_authenticator_usb',         'title' => 'Passkey authenticator USB',                    'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable the use of USB-based authenticators for passkey authentication.', 'help' => 'Toggle this option to allow or restrict the use of USB devices for passkey authentication.',                                       'type' => 'boolean'],
    ['id' => 32, 'package' => 'core', 'code' => 'passkey_authenticator_nfc',         'title' => 'Passkey authenticator NFC',                    'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable the use of NFC-based authenticators for passkey authentication.', 'help' => 'Toggle this option to allow or restrict the use of NFC-enabled devices for passkey authentication.',                              'type' => 'boolean'],
    ['id' => 33, 'package' => 'core', 'code' => 'passkey_authenticator_ble',         'title' => 'Passkey authenticator BLE',                    'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable the use of Bluetooth Low Energy (BLE) authenticators for passkey authentication.', 'help' => 'Toggle this option to allow or restrict the use of BLE devices for passkey authentication.',                         'type' => 'boolean'],
    ['id' => 34, 'package' => 'core', 'code' => 'passkey_authenticator_hybrid',      'title' => 'Passkey authenticator hybrid',                 'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable the use of hybrid authenticators that utilize multiple communication methods.', 'help' => 'Toggle this option to allow or restrict the use of hybrid authenticators for passkey authentication.',                 'type' => 'boolean'],
    ['id' => 35, 'package' => 'core', 'code' => 'passkey_authenticator_internal',    'title' => 'Passkey authenticator internal',               'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Enable or disable the use of internal authenticators built into devices, such as fingerprint scanners.', 'help' => 'Toggle this option to allow or restrict the use of internal authentication methods on devices.',                   'type' => 'boolean'],
    ['id' => 36, 'package' => 'core', 'code' => 'passkey_user-handle',               'title' => 'Mapping for user-specific anonymous user_handle', 'form_control' => 'input', 'section_id' => 3, 'description' => 'User handle for passkey authentication.', 'help' => 'Holds a temporary user_handle as a nonce for each user, used for mapping a current WebAuthn session with an existing user.', 'type' => 'string'],
    ['id' => 37, 'package' => 'core', 'code' => 'time_encoding',                     'title' => 'Time encoding',                               'form_control' => 'select', 'section_id' => 1, 'description' => 'How time values are exchanged between front end and back end.', 'help' => "Use 'frontend' when the UI adjusts values using the browser time zone and sends UTC. Use 'backend' when the backend interprets raw values in its own time zone.", 'type' => 'string'],
    ['id' => 38, 'package' => 'core', 'code' => 'impersonation.allowed',             'title' => 'Impersonation allowed',                       'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow the user to impersonate another user.', 'help' => 'This user-scoped setting controls whether the authenticated user can start impersonation.', 'type' => 'boolean'],
    ['id' => 39, 'package' => 'core', 'code' => 'impersonation.enabled',             'title' => 'Impersonation enabled',                       'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Current impersonation state for the authenticated user.', 'help' => 'When disabled, no impersonation is applied even if a target user is configured.', 'type' => 'boolean'],
    ['id' => 40, 'package' => 'core', 'code' => 'impersonation.user_id',             'title' => 'Impersonation target user',                   'form_control' => 'input',  'section_id' => 3, 'description' => 'Target user identifier for impersonation.', 'help' => 'Stores the user identifier that should be resolved while impersonation is active.', 'type' => 'integer'],
    ['id' => 41, 'package' => 'core', 'code' => 'impersonation.expiry',              'title' => 'Impersonation expiry',                        'form_control' => 'input',  'section_id' => 3, 'description' => 'Expiration timestamp for active impersonation.', 'help' => 'Stores the Unix timestamp after which impersonation is no longer valid.', 'type' => 'integer'],
    ['id' => 42, 'package' => 'core', 'code' => 'auth.passkey.enabled',              'title' => 'Passkey authentication enabled',              'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow users to authenticate with registered passkeys.', 'help' => 'Disable this setting to remove passkey authentication from the sign-in methods offered to users.', 'type' => 'boolean'],
    ['id' => 43, 'package' => 'core', 'code' => 'auth.totp.enabled',                 'title' => 'TOTP authentication enabled',                 'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow users to authenticate with a time-based one-time password as an additional factor.', 'help' => 'Enable this setting before requiring TOTP authentication or allowing users to create TOTP keys.', 'type' => 'boolean'],
    ['id' => 44, 'package' => 'core', 'code' => 'auth.password.totp_required',       'title' => 'TOTP authentication required (MFA)',          'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Require a valid TOTP code after successful password authentication.', 'help' => 'Before enabling this requirement, ensure affected users can enroll a TOTP key or have another account recovery method.', 'type' => 'boolean'],
    ['id' => 45, 'package' => 'core', 'code' => 'totpkey_creation',                  'title' => 'Propose TOTP key creation',                   'form_control' => 'toggle', 'section_id' => 3, 'description' => 'Allow users to enroll a TOTP key for multi-factor authentication.', 'help' => 'When enabled, eligible users can create and validate a TOTP key. Disabling it does not deactivate existing keys.', 'type' => 'boolean'],
    ['id' => 46, 'package' => 'core', 'code' => 'auth.totp.allowed_failed_attempts', 'title' => 'TOTP allowed failed attempts',                'form_control' => 'input', 'section_id' => 3,  'description' => 'The quantity of attempts a user can make to enter the correct authentication code.', 'help' => 'By default 5 attempts are allowed.', 'type' => 'integer']
];

$import_data('core\setting\Setting', [
    'en' => $settings_en,
    'fr' => $rows_from_translations([
        1  => ['title' => 'Séparateur de milliers', 'description' => 'Caractère de séparation de milliers', 'help' => "Caractère de séparation de milliers (e.g. '.', ',', ' ')."],
        2  => ['title' => 'Position de devise', 'description' => 'Position du symbole de monnaie par rapport à sa valeur', 'help' => "Position du symbole de monnaie par rapport à sa valeur ('before', 'after', 'decimal_separator')."],
        3  => ['title' => 'Nombre de décimales (Prix)', 'description' => 'Nombre de décimales pour les prix', 'help' => "Nombre de décimales à conserver pour les champs à usage 'Price'."],
        4  => ['title' => 'Séparateur de décimales', 'description' => "Séparateur de décimales (e.g. '.', ',')", 'help' => "Caractère de séparation entre les parties entière et décimale (e.g. '.', ',')."],
        5  => ['title' => 'Nombre de décimales', 'description' => 'Nombre de décimales', 'help' => "Nombre de décimales à conserver pour les champs de type 'float'."],
        6  => ['title' => 'Format de date', 'description' => 'Format de date', 'help' => "Format pour l'affichage des dates."],
        7  => ['title' => 'Format des heures', 'description' => 'Format des heures', 'help' => "Format pour l'affichage des heures."],
        8  => ['title' => 'Organisation', 'description' => "Organisation de l'installation", 'help' => "Identifiant de l'organisation principale pour l'installation courante."],
        9  => ['title' => 'Format du papier', 'description' => 'Taille papier par défaut', 'help' => "Taille papier par défaut (e.g. 'A4', 'legal_US', 'letter_US')."],
        10 => ['title' => 'Création de compte', 'description' => 'Autoriser la création de compte', 'help' => 'Autoriser les visiteurs à créer un compte utilisateur.'],
        11 => ['title' => 'Importer', 'description' => "Autoriser l'importation", 'help' => 'Autoriser les utilisateurs à importer des données depuis des fichiers (CSV/XLS/XLSX/ODS).'],
        12 => ['title' => 'Exporter', 'description' => "Autoriser l'exportation", 'help' => 'Autoriser les utilisateurs à exporter des données vers des fichiers (CSV/XLS/PDF).'],
        13 => ['title' => 'Devise', 'description' => 'Devise des prix', 'help' => "Devise à utiliser pour les champs à usage 'Price' (ISO 4217)."],
        14 => ['title' => 'Longueur', 'description' => 'Unité de longueur', 'help' => 'Unité par défaut pour les mesures de longueur.'],
        15 => ['title' => 'Poids', 'description' => 'Unité de poids', 'help' => "Unité par défaut pour les mesures de poids (e.g. 'kg' kilogram, 'lb' pound)."],
        16 => ['title' => 'Volume', 'description' => 'Unité de volume', 'help' => "Unité par défaut pour les unités de volume (e.g. 'm3' cubic meter, 'ft3' cubic foot)."],
        17 => ['title' => 'Surface', 'description' => 'Unité de surface', 'help' => "Unité par défaut pour les unités de surface (e.g. 'm2' square meter, 'ft2' square foot)."],
        18 => ['title' => 'Fuseau horaire', 'description' => 'Fuseau horaire sur lequel baser les dates.', 'help' => "Fuseau horaire par défaut à utiliser au niveau de l'application."],
        19 => ['title' => "Proposer la création de la première clé d'accès", 'description' => "Proposer la création de la première clé d'accès après la connexion avec un mot de passe.", 'help' => "Proposé uniquement si l'utilisateur n'a pas encore de clé d'accès."],
        20 => ['title' => 'Passkey relying party id', 'description' => 'Le domaine ou identifiant unique du relying party.', 'help' => "Entrez le domaine ou l'identifiant utilisé par le relying party. Il s'agit généralement d'un nom de domaine (ex. : example.com)."],
        21 => ['title' => 'Passkey relying party name', 'description' => 'Le nom du relying party.', 'help' => "Entrez le nom complet du relying party. Il s'agit généralement du nom du site web ou de l'application (ex. : Exemple Inc.)."],
        22 => ['title' => "Format de clé d'accès Android-Key", 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès Android Key.", 'help' => "Activez cette option pour permettre la vérification des clés d'accès via l'attestation Android Key."],
        23 => ['title' => "Format de clé d'accès Android-SafetyNet", 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès Android SafetyNet.", 'help' => "Activez cette option pour permettre la vérification des clés d'accès via l'attestation Android SafetyNet."],
        24 => ['title' => 'Passkey format Apple', 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès d'Apple.", 'help' => "Activez cette option pour permettre la vérification des clés d'accès via l'attestation d'Apple."],
        25 => ['title' => 'Passkey format FIDO U2F', 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès FIDO U2F.", 'help' => "Activez cette option pour permettre la vérification des clés d'accès via la norme FIDO U2F."],
        26 => ['title' => 'Passkey aucun format', 'description' => "Activer ou désactiver la prise en charge d'une clé d'accès sans format d'attestation.", 'help' => "Activez cette option pour permettre l'utilisation de clés d'accès sans attestation formelle."],
        27 => ['title' => 'Passkey format Packed', 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès Packed.", 'help' => "Activez cette option pour permettre la vérification des clés d'accès utilisant le format Packed."],
        28 => ['title' => 'Passkey format TPM', 'description' => "Activer ou désactiver la prise en charge du format de clé d'accès TPM (Trusted Platform Module).", 'help' => "Activez cette option pour permettre la vérification des clés d'accès via l'attestation TPM."],
        29 => ['title' => 'Passkey vérification utilisateur', 'description' => "Sélectionnez le niveau de vérification de l'utilisateur requis pendant l'authentification par clé d'accès.", 'help' => "Options : 'obligatoire' impose la vérification de l'utilisateur (ex. via biométrie ou code PIN), 'préféré' l'utilise si disponible, et 'découragé' évite la vérification de l'utilisateur."],
        30 => ['title' => 'Passkey cross platform', 'description' => "Sélectionnez le mode d'utilisation des clés d'acccès sur différentes plateformes.", 'help' => "Options : 'all' pour tous les types de clés de sécurité, 'only cross-platform' pour les clés d'accès stockées sur des appareils externes connectés, ou 'only platform' pour les clés d'accès limitées à l'appareil lui-même."],
        31 => ['title' => 'Passkey authenticator USB', 'description' => "Activer ou désactiver l'utilisation d'authentificateurs USB pour l'authentification par clé d'accès.", 'help' => "Activez cette option pour autoriser ou restreindre l'utilisation d'appareils USB pour l'authentification par clé d'accès."],
        32 => ['title' => 'Passkey authenticator NFC', 'description' => "Activer ou désactiver l'utilisation d'authentificateurs NFC pour l'authentification par clé d'accès.", 'help' => "Activez cette option pour autoriser ou restreindre l'utilisation d'appareils compatibles NFC pour l'authentification par clé d'accès."],
        33 => ['title' => 'Passkey authenticator BLE', 'description' => "Activer ou désactiver l'utilisation d'authentificateurs Bluetooth Low Energy (BLE) pour l'authentification par clé d'accès.", 'help' => "Activez cette option pour autoriser ou restreindre l'utilisation d'appareils BLE pour l'authentification par clé d'accès."],
        34 => ['title' => 'Passkey authenticator hybrid', 'description' => "Activer ou désactiver l'utilisation d'authentificateurs hybrides qui utilisent plusieurs méthodes de communication.", 'help' => "Activez cette option pour autoriser ou restreindre l'utilisation d'authentificateurs hybrides pour l'authentification par clé d'accès."],
        35 => ['title' => 'Passkey authenticator internal', 'description' => "Activer ou désactiver l'utilisation d'authentificateurs internes intégrés aux appareils, tels que les scanners d'empreintes digitales.", 'help' => "Activez cette option pour autoriser ou restreindre l'utilisation de méthodes d'authentification internes sur les appareils."],
        36 => ['title' => 'Mapping user_handle anonyme par utilisateur', 'description' => "User handle pour l'authentification par clé d'accès.", 'help' => "Stocke un user_handle temporaire comme nonce pour chaque utilisateur, utilisé pour associer une session WebAuthn courante à un utilisateur existant."],
        37 => ['title' => "Encodage de l'heure", 'description' => "Mode d'échange des heures entre le front-end et le back-end.", 'help' => "Utilisez 'frontend' lorsque l'UI ajuste les valeurs selon le fuseau du navigateur et envoie l'UTC. Utilisez 'backend' lorsque le back-end interprète les valeurs brutes dans son propre fuseau."],
        38 => ['title' => "Impersonation autorisée", 'description' => "Autorise l'utilisateur à impersoner un autre utilisateur.", 'help' => "Ce paramètre propre à l'utilisateur contrôle si l'utilisateur authentifié peut démarrer une impersonation."],
        39 => ['title' => "Impersonation active", 'description' => "État courant de l'impersonation pour l'utilisateur authentifié.", 'help' => "Si ce paramètre est désactivé, aucune impersonation n'est appliquée même si un utilisateur cible est configuré."],
        40 => ['title' => "Utilisateur cible de l'impersonation", 'description' => "Identifiant de l'utilisateur cible pour l'impersonation.", 'help' => "Stocke l'identifiant utilisateur à résoudre lorsque l'impersonation est active."],
        41 => ['title' => "Expiration de l'impersonation", 'description' => "Timestamp d'expiration de l'impersonation active.", 'help' => "Stocke le timestamp Unix après lequel l'impersonation n'est plus valide."],
        42 => ['title' => "Authentification par clé d'accès activée", 'description' => "Autoriser les utilisateurs à s'authentifier avec des clés d'accès enregistrées.", 'help' => "Désactivez ce paramètre pour retirer l'authentification par clé d'accès des méthodes de connexion proposées aux utilisateurs."],
        43 => ['title' => 'Authentification TOTP activée', 'description' => "Autoriser les utilisateurs à s'authentifier avec un mot de passe à usage unique basé sur le temps comme facteur supplémentaire.", 'help' => "Activez ce paramètre avant d'exiger l'authentification TOTP ou d'autoriser les utilisateurs à créer des clés TOTP."],
        44 => ['title' => 'Authentification TOTP requise (MFA)', 'description' => "Exiger un code TOTP valide après une authentification réussie par mot de passe.", 'help' => "Avant d'activer cette exigence, assurez-vous que les utilisateurs concernés peuvent enregistrer une clé TOTP ou disposent d'une autre méthode de récupération de compte."],
        45 => ['title' => "Proposer la création d'une clé TOTP", 'description' => "Autoriser les utilisateurs à enregistrer une clé TOTP pour l'authentification multifacteur.", 'help' => "Lorsque ce paramètre est activé, les utilisateurs éligibles peuvent créer et valider une clé TOTP. Sa désactivation ne désactive pas les clés existantes."],
        46 => ['title' => "TOTP nombre de tentatives autorisées", 'description' => "", 'help' => ""]
    ])
]);

$choice = static function(int $id, int $setting_id, string $name, string $value): array {
    return [
        'id'         => $id,
        'setting_id' => $setting_id,
        'name'       => $name,
        'value'      => $value
    ];
};

$choices_en = [
    $choice(101, 1, 'comma', ','),
    $choice(102, 1, 'point', '.'),
    $choice(201, 2, 'before', 'before'),
    $choice(202, 2, 'after', 'after'),
    $choice(301, 3, '1', '1'),
    $choice(302, 3, '2', '2'),
    $choice(303, 3, '3', '3'),
    $choice(401, 4, 'comma', ','),
    $choice(402, 4, 'point', '.'),
    $choice(501, 5, '1', '1'),
    $choice(502, 5, '2', '2'),
    $choice(503, 5, '3', '3'),
    $choice(601, 6, 'd/m/Y', 'd/m/Y'),
    $choice(602, 6, 'Y-m-d', 'Y-m-d'),
    $choice(603, 6, 'm/d/Y', 'm/d/Y'),
    $choice(701, 7, 'H:i', 'H:i'),
    $choice(801, 8, '1', '1'),
    $choice(901, 9, '21.59 x 27.74', 'US'),
    $choice(902, 9, '21 x 29.7', 'A4'),
    $choice(903, 9, '29.7 x 41.99', 'A3'),
    $choice(904, 9, '14,8 x 21', 'A5'),
    $choice(1301, 13, 'USD', '$'),
    $choice(1302, 13, 'EUR', '€'),
    $choice(1401, 14, 'meter', 'm'),
    $choice(1501, 15, 'kilograms', 'kg'),
    $choice(1601, 16, 'cubic meter', 'm3'),
    $choice(1701, 17, 'square meter', 'm2')
];

$time_zones = [
    'Europe/Amsterdam', 'Europe/Andorra', 'Europe/Athens', 'Europe/Belfast', 'Europe/Belgrade',
    'Europe/Berlin', 'Europe/Bratislava', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest',
    'Europe/Busingen', 'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar',
    'Europe/Guernsey', 'Europe/Helsinki', ['Europe/Isle of Man', 'Europe/Isle_of_Man'], 'Europe/Istanbul',
    'Europe/Jersey', 'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/Ljubljana',
    'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid', 'Europe/Malta', 'Europe/Mariehamn',
    'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Nicosia', 'Europe/Oslo',
    'Europe/Paris', 'Europe/Podgorica', 'Europe/Prague', 'Europe/Riga', 'Europe/Rome',
    'Europe/Samara', ['Europe/San Marino', 'Europe/San_Marino'], 'Europe/Sarajevo', 'Europe/Simferopol',
    'Europe/Skopje', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane',
    'Europe/Tiraspol', 'Europe/Uzhgorod', 'Europe/Vaduz', 'Europe/Vatican', 'Europe/Vienna',
    'Europe/Vilnius', 'Europe/Volgograd', 'Europe/Warsaw', 'Europe/Zagreb', 'Europe/Zaporozhye',
    'Europe/Zurich'
];

foreach($time_zones as $index => $time_zone) {
    $name = is_array($time_zone) ? $time_zone[0] : $time_zone;
    $value = is_array($time_zone) ? $time_zone[1] : $time_zone;
    $choices_en[] = $choice(1801 + $index, 18, $name, $value);
}

$choices_en = array_merge($choices_en, [
    $choice(1860, 29, 'required', 'required'),
    $choice(1861, 29, 'preferred', 'preferred'),
    $choice(1862, 29, 'discouraged', 'discouraged'),
    $choice(1863, 30, 'all', 'all'),
    $choice(1864, 30, 'only cross platform', 'cross-platform'),
    $choice(1865, 30, 'only platform', 'platform'),
    $choice(3701, 37, 'frontend', 'frontend'),
    $choice(3702, 37, 'backend', 'backend')
]);

$import_data('core\setting\SettingChoice', [
    'en' => $choices_en,
    'fr' => $rows_from_translations([
        101 => ['name' => 'virgule'],
        102 => ['name' => 'point'],
        201 => ['name' => 'avant'],
        202 => ['name' => 'après'],
        301 => ['name' => '1'],
        302 => ['name' => '2'],
        303 => ['name' => '3'],
        401 => ['name' => 'virgule'],
        402 => ['name' => 'point'],
        501 => ['name' => '1'],
        502 => ['name' => '2'],
        503 => ['name' => '3'],
        601 => ['name' => 'd/m/Y'],
        602 => ['name' => 'Y-m-d'],
        603 => ['name' => 'm/d/Y'],
        701 => ['name' => 'H:i'],
        801 => ['name' => '1'],
        901 => ['name' => '21,59 x 27,74'],
        902 => ['name' => '21 x 29,7'],
        903 => ['name' => '29,7 x 41,99'],
        904 => ['name' => '14,8 x 21'],
        1301 => ['name' => 'USD'],
        1302 => ['name' => 'EUR'],
        1401 => ['name' => 'mètre'],
        1501 => ['name' => 'kilogramme'],
        1601 => ['name' => 'mètre cube'],
        1701 => ['name' => 'mètre carré'],
        1860 => ['name' => 'requise'],
        1861 => ['name' => 'préférée'],
        1862 => ['name' => 'découragée'],
        1863 => ['name' => 'all'],
        1864 => ['name' => 'seulement cross platform'],
        1865 => ['name' => 'seulement platform'],
        3701 => ['name' => 'front-end'],
        3702 => ['name' => 'back-end']
    ])
]);

// #memo - we do not use lang here to force fallback to DEFAULT_LANG, since these values are not multilang.
$setting_values = [
    ['core', 'locale',   'numbers.thousands_separator',      ','],
    ['core', 'locale',   'currency.symbol_position',         'after'],
    ['core', 'locale',   'currency.decimal_precision',       '2'],
    ['core', 'locale',   'numbers.decimal_separator',        '.'],
    ['core', 'locale',   'numbers.decimal_precision',        '2'],
    ['core', 'locale',   'date_format',                      'd/m/Y'],
    ['core', 'locale',   'time_format',                      'H:i'],
    ['core', 'locale',   'time_encoding',                    'frontend'],
    ['core', 'main',     'company.id',                       '1'],
    ['core', 'main',     'formats.paper',                    'A4'],
    ['core', 'security', 'account_creation',                 '0'],
    ['core', 'security', 'import',                           '1'],
    ['core', 'security', 'export',                           '1'],
    ['core', 'locale',   'currency',                         '€'],
    ['core', 'locale',   'length',                           'm'],
    ['core', 'locale',   'weight',                           'kg'],
    ['core', 'locale',   'volume',                           'm3'],
    ['core', 'locale',   'surface',                          'm2'],
    ['core', 'locale',   'time_zone',                        'Europe/Brussels'],
    ['core', 'security', 'passkey_creation',                 '0'],
    ['core', 'security', 'passkey_rp_id',                    'equal.local'],
    ['core', 'security', 'passkey_rp_name',                  'eQual App'],
    ['core', 'security', 'passkey_format_android-key',       '1'],
    ['core', 'security', 'passkey_format_android-safetynet', '1'],
    ['core', 'security', 'passkey_format_apple',             '1'],
    ['core', 'security', 'passkey_format_fido-u2f',          '1'],
    ['core', 'security', 'passkey_format_none',              '1'],
    ['core', 'security', 'passkey_format_packed',            '1'],
    ['core', 'security', 'passkey_format_tpm',               '1'],
    ['core', 'security', 'passkey_user_verification',        'preferred'],
    ['core', 'security', 'passkey_cross_platform',           'all'],
    ['core', 'security', 'passkey_authenticator_usb',        '1'],
    ['core', 'security', 'passkey_authenticator_nfc',        '1'],
    ['core', 'security', 'passkey_authenticator_ble',        '1'],
    ['core', 'security', 'passkey_authenticator_hybrid',     '1'],
    ['core', 'security', 'passkey_authenticator_internal',   '1'],
    ['core', 'security', 'auth.passkey.enabled',             '0'],
    ['core', 'security', 'auth.totp.enabled',                '0'],
    ['core', 'security', 'auth.password.totp_required',      '0'],
    ['core', 'security', 'totpkey_creation',                 '0']
];

foreach($setting_values as $setting_value) {
    Setting::assert_value(...$setting_value);
}

Setting::assert_value('core', 'security', 'impersonation.enabled', '1', ['user_id' => 1]);
