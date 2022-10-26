<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Check SMTP connectivity using values defined in config file',
    'constants'     => ['EMAIL_SMTP_HOST', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_ACCOUNT_USERNAME', 'EMAIL_SMTP_ACCOUNT_PASSWORD', 'EMAIL_SMTP_ACCOUNT_EMAIL'],
    'providers'     => ['context']
]);

global $result;
$result = '';

// SMTP communication utilities

function send_line($sock, $msg, $append=true) {
    global $result;
    $msg = sprintf("%s\r\n", $msg);
    fwrite($sock, $msg, strlen($msg));
    if($append) $result .= $msg;
}

function read_line($sock, $append=true) {
    global $result;
    $line = fgets($sock, 1024);
    if(!$line) return false;
    if($append) $result .= $line;
    return trim($line);
}


// init vars
list($host, $port, $username, $password) = [
    constant('EMAIL_SMTP_HOST'),
    constant('EMAIL_SMTP_PORT'),
    base64_encode(constant('EMAIL_SMTP_ACCOUNT_USERNAME')),
    base64_encode(constant('EMAIL_SMTP_ACCOUNT_PASSWORD'))
];
list($account, $domain) = explode('@', constant('EMAIL_SMTP_ACCOUNT_EMAIL'));

$attributes = [];

try {
    // check email syntax validity
    if(filter_var(constant('EMAIL_SMTP_ACCOUNT_EMAIL'), FILTER_VALIDATE_EMAIL) === false) {
        throw new Exception('invalid_smtp_account_email', QN_ERROR_INVALID_PARAM);
    }

    // check email domain validity
    if(filter_var($domain, FILTER_VALIDATE_DOMAIN) === false) {
        throw new Exception('invalid_smtp_account_domain', QN_ERROR_INVALID_PARAM);
    }

    // make sure that an MX record is set for domain to which relates the given email account
    if( checkdnsrr($domain, "MX") === false) {
        throw new Exception('no_mx_record_for_smtp_account_domain', QN_ERROR_INVALID_PARAM);
    }

    // make sure the smtp hostname can be resolved to an IP address
    if( ip2long(constant('EMAIL_SMTP_HOST')) === false) {
        $ipv4 = gethostbyname(constant('EMAIL_SMTP_HOST'));
        if($ipv4 == constant('EMAIL_SMTP_HOST')) {
            throw new Exception('invalid_smtp_host', QN_ERROR_INVALID_PARAM);
        }
    }

    // try to establish a connexion with the SMTP host
    $sock = fsockopen($host, $port, $errno, $errstr, 2);
    if (!$sock) {
        throw new Exception('unable_to_conect', QN_ERROR_UNKNOWN);
    }
    // do not wait for respponses more than 1 sec
    stream_set_timeout($sock, 1);

    // most MTAs expect a domain/host name, and the picky ones want the hostname specified here
    // to match the reverse lookup of the IP address.
    send_line($sock, sprintf("EHLO %s", $domain));

    if(!($line = read_line($sock))) {
        throw new Exception('no_response_received', QN_ERROR_UNKNOWN);
    }

    $matches = [];
    preg_match('/^([0-9]{3})([ -])(.*)$/', $line, $matches);

    if( intval($matches[1]) != 220) {
        throw new Exception('invalid_EHLO_response', QN_ERROR_UNKNOWN);
    }

    // SMTP responses can span multiple lines, they will look like
    // ###-First line
    // ###-Second line
    // ### Last line
    //
    // Where ### is the 3-digit status code, and every line but the last has a dash between the
    // code and the text.
    while($line = read_line($sock)) {

        preg_match('/^([0-9]{3})([ -])(.*)$/', $line, $matches);

        if( intval($matches[1]) != 250) {
            throw new Exception('invalid_data_received', QN_ERROR_UNKNOWN);
        }
        $parts = explode(' ', $matches[3], 2);
        $attributes[$parts[0]] = (isset($parts[1]))?$parts[1]:'';
        if($matches[2] == ' ') break;
    }

    // The advertised capabilities of the server in the EHLO response will include
    // the types of AUTH mechanisms that are supported
    if(!isset($attributes['AUTH']) || !in_array($attributes['AUTH'], ['LOGIN PLAIN', 'PLAIN LOGIN']) ) {
        throw new Exception('plain_login_auth_not_available', QN_ERROR_UNKNOWN);
    }

    // request authentication
    send_line($sock, "AUTH LOGIN");
    $line = read_line($sock);

    if(!$line || $line != "334 VXNlcm5hbWU6") {
        throw new Exception('no_username_prompt_received', QN_ERROR_UNKNOWN);
    }

    // send username
    send_line($sock, $username, false);
    $line = read_line($sock);

    if(!$line || $line != "334 UGFzc3dvcmQ6") {
        if(intval(explode(' ', $line)[0]) == 535) {
            throw new Exception('failed_auth_at_user', QN_ERROR_UNKNOWN);
        }
        throw new Exception('no_password_prompt_received', QN_ERROR_UNKNOWN);
    }


    // send password
    send_line($sock, $password, false);
    $line = read_line($sock);

    if(!$line || intval(explode(' ', $line)[0]) != 235) {
        throw new Exception("failed_auth_at_pass", QN_ERROR_UNKNOWN);
    }
}
catch(Exception $e) {
    throw new Exception($e->getMessage(), $e->getCode());
}

fclose($sock);

$providers['context']
    ->httpResponse()
    ->body(['result' => $line])
    ->send();