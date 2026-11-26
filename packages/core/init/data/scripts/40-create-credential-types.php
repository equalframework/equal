<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\CredentialType;

$credential_type_definitions = [
    [
        'code'        => 'ovh-cloud-api',
        'description' => 'Credentials used to access the OVHcloud API.',
        'template'    => [
            'application_key'    => 'OVH_APP_KEY_EXAMPLE_0123456789',
            'application_secret' => 'OVH_APP_SECRET_EXAMPLE_0123456789',
            'consumer_key'       => 'OVH_CONSUMER_KEY_EXAMPLE_0123456789'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants utilisés pour accéder à l'API OVHcloud."
            ]
        ]
    ],
    [
        'code'        => 'microsoft-outlook',
        'description' => 'Microsoft Entra application credentials used to access Outlook.',
        'template'    => [
            'MS_TENANT_ID'             => 'exemple.onmicrosoft.com',
            'MS_OUTLOOK_CLIENT_ID'     => 'cbdd9d60-b691-1026-8748-45e596dbd65e',
            'MS_OUTLOOK_CLIENT_SECRET' => 'xIH8Q~.1tYaUl3IxPm3eRYamBLc4Ptc_3l3vDanQ'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants d'application Microsoft Entra utilisés pour accéder à Outlook."
            ]
        ]
    ],
    [
        'code'        => 'microsoft-business-central',
        'description' => 'Microsoft Entra application credentials used to access Business Central.',
        'template'    => [
            'MS_TENANT_ID'        => 'example.onmicrosoft.com',
            'MS_COMPANY_ID'       => '11111111-2222-3333-4444-555555555555',
            'MS_BC_CLIENT_ID'     => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'MS_BC_CLIENT_SECRET' => 'BC_CLIENT_SECRET_EXAMPLE_0123456789'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants d'application Microsoft Entra utilisés pour accéder à Business Central."
            ]
        ]
    ],
    [
        'code'        => 'stripe',
        'description' => 'Stripe payment service provider API credentials.',
        'template'    => [
            'psp_key' => 'sk_live_xxxxxxxxxxxxx'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants d'API du prestataire de services de paiement Stripe."
            ]
        ]
    ],
    [
        'code'        => 'access-token',
        'description' => 'JSON Web Token (JWT) used as an access credential.',
        'template'    => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJleGFtcGxlLXVzZXIiLCJpc3MiOiJleGFtcGxlLXByb3ZpZGVyIiwiYXVkIjoiZXhhbXBsZS1hcGkifQ.201niysas7nwS8HjPCrY7LLkgZ-g83aThEGOMbXV9Lk',
        'translations' => [
            'fr' => [
                'description' => "Jeton Web JSON (JWT) utilisé comme identifiant d'accès."
            ]
        ]
    ],
    [
        'code'        => 'password',
        'description' => 'Single password used as a credential.',
        'template'    => 'password_example_0123456789',
        'translations' => [
            'fr' => [
                'description' => 'Mot de passe unique utilisé comme identifiant.'
            ]
        ]
    ],
    [
        'code'        => 'custom-json',
        'description' => 'Free-form JSON credentials for providers without a dedicated credential type.',
        'template'    => [
            'provider'   => 'example-provider',
            'api_key'    => 'example_api_key',
            'api_secret' => 'example_api_secret',
            'options'    => [
                'example_option' => 'example_value'
            ]
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants JSON libres pour les fournisseurs sans type d'identifiant dédié."
            ]
        ]
    ],
    [
        'code'        => 'google-document-ai',
        'description' => 'Google Cloud service account credentials for Document AI.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                          => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                      => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'           => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'        => 'document-ai@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID'      => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'         => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_DOCUMENT_AI_LOCATION'                => 'eu',
            'GOOGLE_DOCUMENT_AI_PROCESSOR_ID'            => 'a1b2c3d4e5f60789',
            'GOOGLE_DOCUMENT_AI_PROCESSOR_VERSION'       => 'pretrained-invoice-v2.0-2023-12-06'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Document AI.'
            ]
        ]
    ],
    [
        'code'        => 'google-vertex-ai',
        'description' => 'Google Cloud service account credentials for Vertex AI.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'vertex-ai@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_VERTEX_AI_LOCATION'             => 'europe-west1',
            'GOOGLE_VERTEX_AI_MODEL_ID'             => 'gemini-2.5-flash'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Vertex AI.'
            ]
        ]
    ],
    [
        'code'        => 'google-gemini-api',
        'description' => 'Google API credentials for Gemini.',
        'template'    => [
            'GOOGLE_PROJECT_ID'       => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'   => '987654321012',
            'GOOGLE_API_KEY'          => 'AIzaSyEXAMPLE0123456789FAKE_API_KEY',
            'GOOGLE_GEMINI_MODEL_ID'  => 'gemini-2.5-flash'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants d'API Google pour Gemini."
            ]
        ]
    ],
    [
        'code'        => 'google-cloud-vision',
        'description' => 'Google Cloud service account credentials for Cloud Vision.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'cloud-vision@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_VISION_LOCATION'                => 'eu'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Cloud Vision.'
            ]
        ]
    ],
    [
        'code'        => 'google-cloud-translation',
        'description' => 'Google Cloud service account credentials for Cloud Translation.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'cloud-translation@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_TRANSLATION_LOCATION'           => 'global',
            'GOOGLE_TRANSLATION_MODEL_ID'           => 'general/nmt',
            'GOOGLE_TRANSLATION_GLOSSARY_ID'        => null
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Cloud Translation.'
            ]
        ]
    ],
    [
        'code'        => 'google-maps-platform',
        'description' => 'API credentials for Google Maps Platform.',
        'template'    => [
            'GOOGLE_PROJECT_ID'           => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'       => '987654321012',
            'GOOGLE_MAPS_BROWSER_API_KEY' => 'AIzaSyEXAMPLE0123456789FAKE_BROWSER_KEY',
            'GOOGLE_MAPS_SERVER_API_KEY'  => 'AIzaSyEXAMPLE0123456789FAKE_SERVER_KEY',
            'GOOGLE_MAPS_MAP_ID'          => 'a1b2c3d4e5f60789'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants d'API pour Google Maps Platform."
            ]
        ]
    ],
    [
        'code'        => 'google-cloud-storage',
        'description' => 'Google Cloud service account credentials for Cloud Storage.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'cloud-storage@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_STORAGE_BUCKET'                 => 'example-api-847261-documents'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Cloud Storage.'
            ]
        ]
    ],
    [
        'code'        => 'google-speech-to-text',
        'description' => 'Google Cloud service account credentials for Speech-to-Text.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                       => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                   => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'        => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'     => 'speech-to-text@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID'   => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'      => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_SPEECH_TO_TEXT_LOCATION'          => 'eu',
            'GOOGLE_SPEECH_TO_TEXT_RECOGNIZER_ID'     => '_',
            'GOOGLE_SPEECH_TO_TEXT_MODEL'             => 'chirp_3'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Speech-to-Text.'
            ]
        ]
    ],
    [
        'code'        => 'google-text-to-speech',
        'description' => 'Google Cloud service account credentials for Text-to-Speech.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'text-to-speech@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_TEXT_TO_SPEECH_LANGUAGE_CODE'   => 'fr-FR',
            'GOOGLE_TEXT_TO_SPEECH_VOICE'           => 'fr-FR-Neural2-A'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants de compte de service Google Cloud pour Text-to-Speech.'
            ]
        ]
    ],
    [
        'code'        => 'google-gmail',
        'description' => 'Google OAuth credentials for Gmail.',
        'template'    => [
            'GOOGLE_PROJECT_ID'          => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'      => '987654321012',
            'GOOGLE_OAUTH_CLIENT_ID'     => '987654321012-ab12cd34ef56gh78ij90kl12mn34op56.apps.googleusercontent.com',
            'GOOGLE_OAUTH_CLIENT_SECRET' => 'GOCSPX-EXAMPLE0123456789FAKESECRET',
            'GOOGLE_OAUTH_REDIRECT_URI'  => 'https://example.com/oauth/google/callback',
            'GOOGLE_GMAIL_USER_ID'       => 'me'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants Google OAuth pour Gmail.'
            ]
        ]
    ],
    [
        'code'        => 'google-calendar',
        'description' => 'Google OAuth credentials for Google Calendar.',
        'template'    => [
            'GOOGLE_PROJECT_ID'          => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'      => '987654321012',
            'GOOGLE_OAUTH_CLIENT_ID'     => '987654321012-ab12cd34ef56gh78ij90kl12mn34op56.apps.googleusercontent.com',
            'GOOGLE_OAUTH_CLIENT_SECRET' => 'GOCSPX-EXAMPLE0123456789FAKESECRET',
            'GOOGLE_OAUTH_REDIRECT_URI'  => 'https://example.com/oauth/google/callback',
            'GOOGLE_CALENDAR_ID'         => 'primary'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants Google OAuth pour Google Calendar.'
            ]
        ]
    ],
    [
        'code'        => 'google-drive',
        'description' => 'Google OAuth credentials for Google Drive.',
        'template'    => [
            'GOOGLE_PROJECT_ID'             => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'         => '987654321012',
            'GOOGLE_OAUTH_CLIENT_ID'        => '987654321012-ab12cd34ef56gh78ij90kl12mn34op56.apps.googleusercontent.com',
            'GOOGLE_OAUTH_CLIENT_SECRET'    => 'GOCSPX-EXAMPLE0123456789FAKESECRET',
            'GOOGLE_OAUTH_REDIRECT_URI'     => 'https://example.com/oauth/google/callback',
            'GOOGLE_DRIVE_FOLDER_ID'        => '1AbCdEfGhIjKlMnOpQrStUvWxYz012345',
            'GOOGLE_DRIVE_SHARED_DRIVE_ID'  => '0AExampleSharedDriveUk9PVA'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants Google OAuth pour Google Drive.'
            ]
        ]
    ],
    [
        'code'        => 'google-sheets',
        'description' => 'Google OAuth credentials for Google Sheets.',
        'template'    => [
            'GOOGLE_PROJECT_ID'             => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'         => '987654321012',
            'GOOGLE_OAUTH_CLIENT_ID'        => '987654321012-ab12cd34ef56gh78ij90kl12mn34op56.apps.googleusercontent.com',
            'GOOGLE_OAUTH_CLIENT_SECRET'    => 'GOCSPX-EXAMPLE0123456789FAKESECRET',
            'GOOGLE_OAUTH_REDIRECT_URI'     => 'https://example.com/oauth/google/callback',
            'GOOGLE_SHEETS_SPREADSHEET_ID' => '1AbCdEfGhIjKlMnOpQrStUvWxYz0123456789'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Identifiants Google OAuth pour Google Sheets.'
            ]
        ]
    ],
    [
        'code'        => 'google-analytics-data',
        'description' => 'Google Cloud service account credentials for the Google Analytics Data API.',
        'template'    => [
            'GOOGLE_PROJECT_ID'                     => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'                 => '987654321012',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_ID'      => '109876543210987654321',
            'GOOGLE_SERVICE_ACCOUNT_CLIENT_EMAIL'   => 'analytics@example-api-847261.iam.gserviceaccount.com',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY_ID' => 'a1b2c3d4e5f60789a1b2c3d4e5f60789a1b2c3d4',
            'GOOGLE_SERVICE_ACCOUNT_PRIVATE_KEY'    => "-----BEGIN PRIVATE KEY-----\n[FAKE_PRIVATE_KEY_DATA]\n-----END PRIVATE KEY-----\n",
            'GOOGLE_ANALYTICS_PROPERTY_ID'          => '123456789'
        ],
        'translations' => [
            'fr' => [
                'description' => "Identifiants de compte de service Google Cloud pour l'API Google Analytics Data."
            ]
        ]
    ],
    [
        'code'        => 'google-recaptcha',
        'description' => 'Google reCAPTCHA site and secret keys.',
        'template'    => [
            'GOOGLE_PROJECT_ID'           => 'example-api-847261',
            'GOOGLE_PROJECT_NUMBER'       => '987654321012',
            'GOOGLE_RECAPTCHA_SITE_KEY'   => '6LcEXAMPLE0123456789FAKE_SITE_KEY',
            'GOOGLE_RECAPTCHA_SECRET_KEY' => '6LcEXAMPLE0123456789FAKE_SECRET_KEY'
        ],
        'translations' => [
            'fr' => [
                'description' => 'Clés de site et secrète Google reCAPTCHA.'
            ]
        ]
    ]
];

foreach($credential_type_definitions as $definition) {
    $translations = $definition['translations'];
    $template = $definition['template'];
    unset($definition['translations'], $definition['template']);

    if(is_array($template)) {
        $definition['secret_template'] = json_encode(
            $template,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }
    elseif(is_string($template)) {
        $definition['secret_template'] = $template;
    }
    else {
        throw new Exception('invalid_credential_type_template', EQ_ERROR_INVALID_CONFIG);
    }

    $credential_type = CredentialType::search(['code', '=', $definition['code']]);
    if(count($credential_type->ids())) {
        $credential_type->update($definition, 'en');
    }
    else {
        $credential_type = CredentialType::create($definition, 'en');
    }

    foreach($translations as $lang => $translation) {
        $credential_type->update($translation, $lang);
    }
}
