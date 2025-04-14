<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;

// #memo - we do not use lang here to force fallback to DEFAULT_LANG, since these values are not multilang

Setting::assert_value('core', 'locale', 'numbers.thousands_separator', ',');
Setting::assert_value('core', 'locale', 'currency.symbol_position', 'after');
Setting::assert_value('core', 'locale', 'currency.decimal_precision', '2');
Setting::assert_value('core', 'locale', 'numbers.decimal_separator', '.');
Setting::assert_value('core', 'locale', 'numbers.decimal_precision', '2');
Setting::assert_value('core', 'locale', 'date_format', 'd/m/Y');
Setting::assert_value('core', 'locale', 'time_format', 'H:i');
Setting::assert_value('core', 'main', 'company.id', '1');
Setting::assert_value('core', 'main', 'formats.paper', 'A4');
Setting::assert_value('core', 'security', 'account_creation', '0');
Setting::assert_value('core', 'security', 'import', '1');
Setting::assert_value('core', 'security', 'export', '1');
Setting::assert_value('core', 'locale', 'currency', '€');
Setting::assert_value('core', 'locale', 'length', 'm');
Setting::assert_value('core', 'locale', 'weight', 'kg');
Setting::assert_value('core', 'locale', 'volume', 'm3');
Setting::assert_value('core', 'locale', 'surface', 'm2');
Setting::assert_value('core', 'locale', 'time_zone', 'Europe/Brussels');
Setting::assert_value('core', 'security', 'passkey_creation', '0');
Setting::assert_value('core', 'security', 'passkey_rp_id', 'equal.local');
Setting::assert_value('core', 'security', 'passkey_rp_name', 'eQual App');
Setting::assert_value('core', 'security', 'passkey_format_android-key', '1');
Setting::assert_value('core', 'security', 'passkey_format_android-safetynet', '1');
Setting::assert_value('core', 'security', 'passkey_format_apple', '1');
Setting::assert_value('core', 'security', 'passkey_format_fido-u2f', '1');
Setting::assert_value('core', 'security', 'passkey_format_none', '1');
Setting::assert_value('core', 'security', 'passkey_format_packed', '1');
Setting::assert_value('core', 'security', 'passkey_format_tpm', '1');
Setting::assert_value('core', 'security', 'passkey_user_verification', 'preferred');
Setting::assert_value('core', 'security', 'passkey_cross_platform', 'all');
Setting::assert_value('core', 'security', 'passkey_authenticator_usb', '1');
Setting::assert_value('core', 'security', 'passkey_authenticator_nfc', '1');
Setting::assert_value('core', 'security', 'passkey_authenticator_ble', '1');
Setting::assert_value('core', 'security', 'passkey_authenticator_hybrid', '1');
Setting::assert_value('core', 'security', 'passkey_authenticator_internal', '1');
