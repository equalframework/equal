<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\http;

use qinoa\http\HttpMessage;

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
                    $this->setStatusCode($parts[0]);
                }
                else {
                    $this->setProtocol($parts[0]);
                }
            }
        }
                
    }
    /**
     * Sends a HTTP response to the output stream (stdout)
     * This method can only be used with PHP context 
     * and is used as a helper to build the actual response of the current request
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
            if($header == 'Content-Length') continue;
            // cookies are handled in a second pass
            if($header == 'Cookie') continue;
            header($header.': '.$value);
        }
        
        // set cookies, if any
        foreach($this->getHeaders()->getCookies() as $cookie => $value) {            
            $host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
            $host_parts = explode('.', $host);
            $tld = array_pop($host_parts);
            $domain = array_pop($host_parts);
            // default validity to 1 year (according to cookies legislation - as of 2018)
            setcookie($cookie, $value, time()+60*60*24*365, '/', $domain.'.'.$tld);
            // equivalent to 
            // header("Set-Cookie: cookiename=cookievalue; expires=Tue, 06-Jan-2018 23:39:49 GMT; path=/; domain=example.net");
        }
        
        // output body
        $body = $this->getBody();

        if(is_array($body)) {
            switch($this->getHeaders()->getContentType()) {
            case 'application/vnd.api+json':                
            case 'application/json':
                $body = json_encode($body, JSON_PRETTY_PRINT);
                break;
            case 'application/javascript':            
            case 'text/javascript':
                // JSON-P
// todo                
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
        header('Content-Length: '.strlen($body));
        print($body);
        // we return a pointer to current instance for consistency, but no output should be emitted after this point
        return $this;        
    }
    
}