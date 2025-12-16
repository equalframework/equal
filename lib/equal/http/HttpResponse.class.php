<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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

    private static function arrayToXml(\SimpleXMLElement &$object, array $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if(is_numeric($key)) {
                    $new_object = $object->addChild('elem');
                    $new_object->addAttribute('id', $key);
                }
                else $new_object = $object->addChild($key);
                self::arrayToXml($new_object, $value);
            }
            else $object->addChild($key, $value);
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
            $params = $this->headers()->getCookieParams($cookie);
            // Expiration date — defines when the cookie is deleted.
            $expires  = $params['expires'] ?? (time() + 60*60*24*365);
            // Path scope — '/' means the cookie is sent for all paths.
            $path     = $params['path'] ?? '/';
            // Domain scope — empty restricts it to the current host only.
            $domain   = $params['domain'] ?? '';
            // Secure flag — only sent over HTTPS when true.
            $secure   = $params['secure'] ?? false;
            // HttpOnly — hides cookie from JavaScript (server only).
            $httponly = $params['httponly'] ?? false;
            // SameSite — controls cross-site sending ('Lax', 'Strict', 'None').
            $samesite = $params['samesite'] ?? 'Lax';
            // #memo - empty domain will make the cookie available for original domain only (RFC 6265)
            // equivalent to header("Set-Cookie: cookiename=cookievalue; expires=Tue, 06-Jan-2018 23:39:49 GMT; path=/; domain=example.net");
            setcookie($cookie, $value, [
                'expires'   => $expires,
                'path'      => $path,
                'domain'    => $domain,
                'secure'    => $secure,
                'httponly'  => $httponly,
                'samesite'  => $samesite,
            ]);
        }

        // retrieve body
        $body = $this->body();

        // if body is an associative array, try to convert it into plain text
        if(is_array($body)) {

            switch($this->headers()->getContentType()) {
            case 'application/vnd.api+json':
            case 'application/x-json':
            case 'application/json':
                $json_flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
                if($this->headers()->getCharset() == 'UTF-8') {
                    $json_flags |= JSON_UNESCAPED_UNICODE;
                }
                $body = json_encode($body, $json_flags);
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
                $xml = new \SimpleXMLElement('<root/>');
                self::arrayToXml($xml, $body);
                $body = $xml->asXML();
                break;
            default:
                $body = http_build_query($body);
            }
        }
        else {
            switch($this->headers()->getContentType()) {
            case 'text/csv':
                if($this->headers()->getCharset() === 'UTF-8') {
                    $body = "\xEF\xBB\xBF" . $body;
                }
                break;
            }
        }
        // set header accordingly to body size
        header('Content-Length: ' . strlen($body));
        // output body
        print($body);
        // return a pointer to current instance for consistency, but no output should be emitted after this point
        return $this;
    }

}
