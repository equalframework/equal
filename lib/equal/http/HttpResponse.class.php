<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\http;

use equal\http\HttpMessage;

class HttpResponse extends HttpMessage {

    public function __construct($headline, $headers=[], $body='') {
        parent::__construct($headline, $headers, $body);

        // parse headline
        $parts = explode(' ', $headline, 2);

        // retrieve status and/or protocol
        if(isset($parts[1])) {
            $this->setStatus($parts[1]);
            $this->setProtocol($parts[0]);
        }
        else {
            if(isset($parts[0])) {
                if(is_numeric($parts[0])) {
                    $this->setStatus($parts[0]);
                }
                else {
                    $this->setProtocol($parts[0]);
                }
            }
        }

    }

    /**
     * Sends a HTTP response to the output stream (stdout).
     * This method is meant to be used in conjunction with PHP context,
     * and serves as helper to build the actual response of the current request.
     *
     */
    public function send() {
        // reset default headers, if any
        header_remove();

        // set status-line
        header($this->getProtocol().' '.$this->getStatus());
        // CGI SAPI
        header('Status:  '.$this->getStatus());
        // set headers
        $headers = $this->getHeaders(true);
        foreach($headers as $header => $value) {
            // we'll set content length afterward
            if($header == 'Content-Length') {
                continue;
            }
            // cookies are handled in a second pass
            if($header == 'Cookie') {
                continue;
            }
            header($header.': '.$value);
        }

        // set cookies, if any
        foreach($this->headers()->getCookies() as $cookie => $value) {
            $hostname = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
            // make sure the hostname does not contain a port number
            $hostname = substr($hostname.':', 0, strpos($hostname.':', ':'));
            $params = $this->headers()->getCookieParams($cookie);
            $expires = (isset($params['expires']))?$params['expires']:time()+60*60*24*365;
            $path = (isset($params['path']))?$params['path']:'/';
            $domain = (isset($params['domain']))?$params['domain']:$hostname;
            $secure = (isset($params['secure']))?$params['secure']:false;
            $httponly = (isset($params['httponly']))?$params['httponly']:false;
            // equivalent to header("Set-Cookie: cookiename=cookievalue; expires=Tue, 06-Jan-2018 23:39:49 GMT; path=/; domain=example.net");
            setcookie($cookie, $value, $expires, $path, $domain, $secure, $httponly);
            // #todo - handle samesite (as of PHP 7.3)
            /*
            $samesite = (isset($params['samesite']) && in_array($params['samesite'], ['None', 'Lax', 'Strict']))?$params['samesite']:'None';
            setcookie($name, $value, [
                'expires'   => $expires,
                'path'      => $path,
                'domain'    => $domain,
                'secure'    => $secure,
                'httponly'  => $httponly,
                'samesite'  => 'None',
            ]);
            */
        }

        // retrieve body
        $body = $this->body();

        // if body is an associative array, try to convert it into plain text
        if(is_array($body)) {

            switch($this->headers()->getContentType()) {
            case 'application/vnd.api+json':
            case 'application/x-json':
            case 'application/json':
                $body = json_encode($body, JSON_PRETTY_PRINT);
                if($body === false) {
                    throw new \Exception('invalid_json_input', QN_ERROR_UNKNOWN);
                }
                break;
            case 'application/javascript':
            case 'text/javascript':
                // JSON-P
                // todo
                break;
            case 'text/csv':
                // todo
            case 'text/html':
            case 'text/plain':
                // raw content
                break;
            case 'text/xml':
            case 'application/xml':
            case 'text/xml, application/xml':
                function to_xml(\SimpleXMLElement &$object, array $data) {
                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            if(is_numeric($key)) {
                                $new_object = $object->addChild('elem');
                                $new_object->addAttribute('id', $key);
                            }
                            else $new_object = $object->addChild($key);
                            to_xml($new_object, $value);
                        }
                        else $object->addChild($key, $value);
                    }
                }
                $xml = new \SimpleXMLElement('<root/>');
                to_xml($xml, $body);
                $body = $xml->asXML();
                break;
            default:
                $body = http_build_query($body);
            }
        }
        else {
            switch($this->headers()->getContentType()) {
            case 'text/csv':
                if($this->headers()->getCharset() == 'UTF-8') {
                     $body = "\xEF\xBB\xBF".$body;
                }
                break;
            }
        }
        // set header accordingly to body size
        header('Content-Length: '.strlen($body));
        // output body
        print($body);
        // we return a pointer to current instance for consistency, but no output should be emitted after this point
        return $this;
    }

}