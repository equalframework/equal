<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\http;

use equal\http\HttpMessage;
use equal\http\HttpUri;
use equal\http\MobileDetect;

class HttpRequest extends HttpMessage {

    public function __construct($headline='', $headers=[], $body='') {
        parent::__construct($headline, $headers, $body);
        // parse headline
        $re = '/((GET|POST|PUT|DELETE|PATCH|OPTIONS) )?( )?([^ ]*)(( )HTTP.*)?/i';
        preg_match($re, $headline, $matches);

        if(isset($matches[2]) && strlen($matches[2])) {
            $method = $matches[2];
        }

        if(isset($matches[4]) && strlen($matches[4])) {
            $uri = $matches[4];
        }
        if(isset($matches[7]) && strlen($matches[7])) {
            $protocol = $matches[7];
        }

        // 1) retrieve protocol
        if(isset($protocol)) {
            $this->setProtocol($protocol);
        }
        // 2) retrieve URI and host
        if(isset($uri)) {
            // URI ?
            if(substr($uri, 0, 1) == '/') {
                // relative URI
                $host = $this->getHeader('Host', null);
                if(!is_null($host)) {
                    $host_parts = explode(':', $host);
                    $host = $host_parts[0];
                    // check Host header for a port number (see RFC2616)
                    // @link https://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.23
                    $port = isset($host_parts[1])?trim($host_parts[1]):80;
                    $scheme = ($port==443)?'https':'http';
                    $uri = $scheme.'://'.$host.':'.$port.$uri;
                    $this->setUri($uri);
                }
            }
            else {
                // absolute URI
                $this->setUri($uri);
            }

            if(!HttpUri::isValid($uri)) {
                echo 'invalid';
                // #todo : should we raise an Exception ?
            }
        }
        // 3) retrieve method
        if(isset($method)) {
            // method ?
            if(in_array($method, self::$HTTP_METHODS) ) {
                $this->setMethod($method);
            }
        }

    }


    public function send() {
        $response = null;

        $uri = (string) $this->getUri();

        if(strlen($uri) > 0) {
            $method = $this->getMethod();

            $http_options = [];
            $additional_headers = [
                // invalidate keep-alive behavior
                'Connection'        => 'close',
                // simulate a XHR request
                'X-Requested-With'  => 'XMLHttpRequest',
                // accept any content type
                'Accept'            => '*/*',
                // ask for unicode charset
                'Accept-Charset'    => 'UTF-8'
            ];
            // retrieve content type
            $content_type = $this->getHeaders()->getContentType();

            if(strlen($content_type) <= 0 && in_array($method,['GET', 'POST'])) {
                // fallback to form encoded data
                $content_type = 'application/x-www-form-urlencoded';
            }

            // retrieve content
            $body = $this->body();
            // force parameters to the URI in case of GET request (which shouldn't hold a body)
            // (@see RFC7231 : "A payload within a GET request message has no defined semantics")
            if($method == 'GET') {
                if(is_array($body)) {
                    $body = http_build_query($body);
                }
                else {
                    $body = urlencode($body);
                }
                $uri = explode('?', $uri)[0].'?'.$body;
                $body = '';
            }
            elseif(is_array($body)) {
                switch($content_type) {
                    case 'application/vnd.api+json':
                    case 'application/x-json':
                    case 'application/json':
                        $body = json_encode($body, JSON_PRETTY_PRINT);
                        break;
                    case 'application/x-www-form-urlencoded':
                    default:
                        $body = http_build_query($body);
                }
            }

            // compute content length
            $body_length = strlen($body);
            // set content and content-type if relevant
            if($body_length > 0) {
                $http_options['content'] = $body;
                $additional_headers['Content-Type'] = $content_type;
            }
            // set content-length (might be 0)
            $additional_headers['Content-Length'] = $body_length;
            // merge manually defined headers with additional headers (later overwrites the former)
            $headers = array_merge((array) $this->getHeaders(true), $additional_headers);
            // adapt headers to fit into a numerically indexed array
            $headers = array_map(function ($header, $value) {return "$header: $value";}, array_keys($headers), $headers);
            // build the HTTP options array
            $http_options = array_merge($http_options, [
                    'method'            => $method,
                    'request_fulluri'   => true,
                    'header'            => $headers,
                    'ignore_errors'     => true,
                    'timeout'           => (defined('HTTP_REQUEST_TIMEOUT'))?constant('HTTP_REQUEST_TIMEOUT'):10,
                    'protocol_version'  => $this->getProtocolVersion()
                ]);
            // create the HTTP stream context
            $context = stream_context_create([
                    'http' => $http_options
                ]);

            // send request
            $data = @file_get_contents(
                    $uri,
                    false,
                    $context
                );
            // build HTTP response object
            if(isset($http_response_header[0])) {
                $response_status = $http_response_header[0];
                unset($http_response_header[0]);
                $headers = [];
                foreach($http_response_header as $line) {
					list($header, $value) = ['', ''];
                    $parts = array_map('trim', explode(':', $line));
					if(isset($parts[0])) $header = $parts[0];
					if(isset($parts[1])) $value = $parts[1];
					if(strpos($header, 'HTTP/1.1') === 0) {
						$response_status = $header;
					}
					else {
						$headers[$header] = $value;
					}
                }
                $response = new HttpResponse($response_status, $headers, $data);
            }
            else {
                throw new \Exception('unable to send HTTP request', QN_ERROR_UNKNOWN);
            }
        }
        return $response;
    }

}