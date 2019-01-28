<?php

list($params, $providers) = announce([
    'description'   => 'Check SMTP connectivity using values defined in config file',
    'constants'     => ['FILE_STORAGE_MODE', 'FILE_STORAGE_DIR', 'ROUTING_METHOD', 'ROUTING_CONFIG_DIR'],
    'providers'     => ['context']
]);

global $result;
$result = '';

// define 
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
    EMAIL_SMTP_HOST, 
    EMAIL_SMTP_PORT,
    base64_encode(EMAIL_SMTP_ACCOUNT_USERNAME),
    base64_encode(EMAIL_SMTP_ACCOUNT_PASSWORD)
];
list($account, $domain) = explode('@', EMAIL_SMTP_ACCOUNT_EMAIL);

$attributes = [];

$sock = fsockopen($host, $port, $errno, $errstr, 2);
stream_set_timeout($sock, 1);


try {
    // most MTAs expect a domain/host name, and the picky ones want the hostname specified here
    // to match the reverse lookup of the IP address.
    send_line($sock, sprintf("EHLO %s", $domain));


    if(!($line = read_line($sock))) {
        throw new Exception('No response received', QN_ERROR_UNKNOWN);        
    }

    $matches = [];
    preg_match('/^([0-9]{3})([ -])(.*)$/', $line, $matches);

    if( intval($matches[1]) != 220) {
        throw new Exception('Abnormal EHLO response received: ' . $line, QN_ERROR_UNKNOWN);
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
            throw new Exception('Abnormal data received: ' . $line, QN_ERROR_UNKNOWN);
        }
        $parts = explode(' ', $matches[3], 2);
        $attributes[$parts[0]] = (isset($parts[1]))?$parts[1]:'';
        if($matches[2] == ' ') break;
    }

    // The advertised capabilities of the server in the EHLO response will include 
    // the types of AUTH mechanisms that are supported
    if(!isset($attributes['AUTH']) || !in_array($attributes['AUTH'], ['LOGIN PLAIN', 'PLAIN LOGIN']) ) {
        throw new Exception('Plain login authentication not supported', QN_ERROR_UNKNOWN);
    }

    // request authentication
    send_line($sock, "AUTH LOGIN");
    $line = read_line($sock);

    if(!$line || $line != "334 VXNlcm5hbWU6") {
        throw new Exception('No prompt for username received', QN_ERROR_UNKNOWN);    
    }

    // send username
    send_line($sock, $username, false);
    $line = read_line($sock);

    if(!$line || $line != "334 UGFzc3dvcmQ6") {
        if(intval(explode(' ', $line)[0]) == 535) {
            throw new Exception('Authentication failed', QN_ERROR_UNKNOWN);
        }        
        throw new Exception('No prompt for password received', QN_ERROR_UNKNOWN);
    }


    // send password
    send_line($sock, $password, false);
    $line = read_line($sock);

    if(!$line || intval(explode(' ', $line)[0]) != 235) {
        throw new Exception('Unable to authenticate with provided credentials', QN_ERROR_UNKNOWN);
    }
}
catch(Exception $e) {
    throw new Exception($e->getMessage(), $e->getCode());
}

fclose($sock);

$providers['context']->httpResponse()->body(['result' => $result])->send();