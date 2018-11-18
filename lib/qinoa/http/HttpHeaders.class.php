<?php
/* 
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2017, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace qinoa\http;


class HttpHeaders {

    protected static $MIME_TYPES = [
        'MIME_HTML'  => ['text/html', 'application/xhtml+xml'],
        'MIME_TXT'   => ['text/plain'],
        'MIME_JS'    => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'MIME_CSS'   => ['text/css'],
        'MIME_JSON'  => ['application/json', 'application/x-json'],
        'MIME_XML'   => ['text/xml', 'application/xml', 'application/x-xml'],
        'MIME_RDF'   => ['application/rdf+xml'],
        'MIME_ATOM'  => ['application/atom+xml'],
        'MIME_RSS'   => ['application/rss+xml'],
        'MIME_FORM'  => ['application/x-www-form-urlencoded'],
        'MIME_PDF'   => ['application/pdf'],
        'MIME_JPEG'  => ['image/jpeg']
    ];
    
    private $headers;
    
    public function __construct($headers) {
        $this->setHeaders($headers);
    }
    
    
    public function set($header, $value) {
        $header = self::normalizeName($header);
        $this->headers[$header] = $value;
        return $this;
    }
        
    public function setHeaders($headers) {
        if(!isset($this->headers)) $this->headers = [];
        foreach($headers as $header => $value) {
            $this->set($header, $value);
        }
        return $this;
    }
    
    public function setCookie($cookie, $value) {
        $cookies = $this->getCookies();
        $cookies[$cookie] = $value;        
        $rawcookie_parts = array_map(function ($cookie, $value) { return "$cookie=$value"; }, array_keys($cookies), $cookies);
        $this->set('Cookie', implode('; ', $rawcookie_parts));        
        return $this;
    }    

    public function setCharset($charset) {
        if(isset($this->headers['Accept-Charset'])) {
            return $this->set('Accept-Charset', $charset);
        }
        else {
            $content_type = $this->getContentType();
            return $this->set('Content-Type', $content_type.'; charset='.strtoupper($charset));
        }
    }

    public function setContentType($content_type) {
        if(isset($this->headers['Accept'])) {
            return $this->set('Accept', $content_type);
        }
        else {
            $charset = $this->getCharset();
            return $this->set('Content-Type', $content_type.'; charset='.$charset);
        }
    }
    
    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * This method returns a comma-separated string of the values for a single header.
     *
     * If the header does not appear in the message, $default value is returned
     *
     * @param string $header Case-insensitive header field name.
     * @param string $default Default value to return in case header is not present.
     *     
     * @return string The raw value of specified header
     */    
    public function get($header, $default=null) {
        $res = $default;
        if(isset($this->headers[$header])) $res = $this->headers[$header];
        else {
            $normaliazed_header = self::normalizeName($header);
            if(isset($this->headers[$normaliazed_header])) $res = $this->headers[$normaliazed_header];
        }
        return $res;
    }
    
    public function getHeaders() {
        return $this->headers;
    }    

    public function getCookies()  {
        $cookies = [];
        if(isset($this->headers['Cookie']) && strlen($this->headers['Cookie']) > 0) {
            // general syntax: key=value
            // example: Cookie: _gid=GA1.2.810697551.1513508707; PHPSESSID=3hhf6qekt89k9v0hkllvu4u815 
            $lines = explode(';', $this->headers['Cookie']);
            foreach($lines as $line) {
                $parts = explode('=', $line);
                // skip invalid items
                if(count($parts) != 2) continue;
                $cookies[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $cookies;
    }

    public function getCookie($cookie, $default=null)  {
        $cookies = $this->getCookies();
        if(isset($cookies[$cookie])) {
            return $cookies[$cookie];
        }
        return $default;
    }
    
// todo: make explicit distinction between charset used in the message and expected charset in response
    
    public function getCharsets()  {
        $charsets = [];
        if(isset($this->headers['Accept-Charset'])) {
            // general syntax: character_set [q=qvalue]
            // example: Accept-Charset: iso-8859-5, unicode-1-1; q=0.8
            $parts = explode(',', $this->headers['Accept-Charset']);
            if(count($parts)) {
                $charsets = array_map(function($a) { return trim(explode(';', $a)[0]); }, $parts);
            }
        }
        else if(isset($this->headers['Content-Type'])) {
            // general syntax: media-type
            // example: Content-Type: text/html; charset=ISO-8859-4
            $parts = explode(';', $this->headers['Content-Type']);
            if(count($parts) > 1) {
                list($key, $val) = explode('=', $parts[1]);
                if(trim($key) == 'charset') {
                    $charsets[] = trim($val);
                }                
            }
        }
        if(empty($charsets)) {
            $charsets = (array) 'utf-8';
        }
        return $charsets;
    }
    
    // preferred charset
    public function getCharset() {
        $charsets = $this->getCharsets();
        return $charsets[0];        
    }
    
    public function getLanguages()  {
        $languages = (array) 'en';
        if(isset($this->headers['Accept-Language'])) {
            // general syntax: language [q=qvalue]
            // example: Accept-Language: da, en-gb;q=0.8, en;q=0.7
            $parts = explode(',', $this->headers['Accept-Language']);
            if(count($parts)) {
                $languages = array_map(function($a) { return trim(explode(';', $a)[0]); }, $parts);
            }
        }
        else if(isset($this->headers['Content-Language'])) {
            // general syntax: language-tag
            // example: Content-Language: fr, en
            $parts = explode(',', $this->headers['Content-Language']);
            if(count($parts)) {
                $languages = array_map(function($a) { return trim($a); }, $parts);
            }
        }

        return $languages;
    }

    // preferred languages
    public function getLanguage() {
        $languages = $this->getLanguages();
        return $languages[0];        
    }
    
    
    /**
     * Returns the client IP addresses.
     *
     * List is based on proxies order set in the header.
     *
     *
     * @return array The client IP addresses
     *
     * @see getIpAddress()
     */
    private function getIpAddresses() {
        $client_ips = array();

        if (isset($this->headers['Forwarded'])) {
            preg_match_all('{(for)=("?\[?)([a-z0-9\.:_\-/]*)}', $this->headers['X-Forwarded-For'], $matches);
            $client_ips = $matches[3];
        } 
        elseif (isset($this->headers['X-Forwarded-For'])) {
            $client_ips = array_map('trim', explode(',', $this->headers['X-Forwarded-For']));
        }

        foreach ($client_ips as $key => $client_ip) {
            // remove port, if any
            if (preg_match('{((?:\d+\.){3}\d+)\:\d+}', $client_ip, $match)) {
                $client_ips[$key] = $client_ip = $match[1];
            }
            // remove invalid adresses
            if (!filter_var($client_ip, FILTER_VALIDATE_IP)) {
                unset($client_ips[$key]);
                continue;
            }
        }

        // Now the IP chain contains only untrusted proxies and the client IP
        return array_reverse($client_ips) ;
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * @return string The client IP address
     *
     * @see getIpAddresses()
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getIpAddress() {
        $ipAddresses = $this->getIpAddresses();
        return (count($ipAddresses))?$ipAddresses[0]:'';
    }    


    /**
     * Gets the format associated with the request.
     *
     * @return string|null The format (null if no content type is present)
     */
    public function getContentType() {        
        $content_type = '';
        // use content-type header, if defined
        if(isset($this->headers['Content-Type'])) {
            // general syntax: media-type
            // example: Content-Type: text/html; charset=ISO-8859-4
            $content_type = trim(explode(';', $this->headers['Content-Type'])[0]);
        }
        else if(isset($this->headers['Accept'])) {
            // general syntax: type/subtype [q=qvalue]
            // example: Accept: text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c
            $parts = explode(',', $this->headers['Accept']);
            if(count($parts)) {
                $content_type = array_map(function($a) { return trim(explode(';', $a)[0]); }, $parts);
            }
        }
        return $content_type;
    }

  

    /**
     *
     * Dominant rule consists of separate words with a dash (-), and write words lowercase using a capital letter for first character
     * But some commonly used names do not comply with this rule Content-MD5, ETag, P3P, DNT, X-ATT-DeviceId, ...
     * Besides, user might define some custom header and expect to retrieve them unaltered
     
     * List of official and common non-standards fields from Wikipedia
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
     */
    public static function normalizeName($name) {
        static $fields = null;
        
        if(is_null($fields)) {
            $fields = [
                'accept'                        => 'Accept',
                'acceptcharset'                 => 'Accept-Charset',
                'acceptdatetime'                => 'Accept-Datetime',
                'acceptencoding'                => 'Accept-Encoding',
                'acceptlanguage'                => 'Accept-Language',
                'acceptpatch'                   => 'Accept-Patch',
                'acceptranges'                  => 'Accept-Ranges',
                'accesscontrolallowcredentials' => 'Access-Control-Allow-Credentials',
                'accesscontrolallowheaders'     => 'Access-Control-Allow-Headers',
                'accesscontrolallowmethods'     => 'Access-Control-Allow-Methods',
                'accesscontrolalloworigin'      => 'Access-Control-Allow-Origin',
                'accesscontrolexposeheaders'    => 'Access-Control-Expose-Headers',
                'accesscontrolmaxage'           => 'Access-Control-Max-Age',
                'accesscontrolrequestheaders'   => 'Access-Control-Request-Headers',
                'accesscontrolrequestmethod'    => 'Access-Control-Request-Method',
                'age'                           => 'Age',
                'allow'                         => 'Allow',
                'altsvc'                        => 'Alt-Svc',
                'authorization'                 => 'Authorization',
                'cachecontrol'                  => 'Cache-Control',
                'connection'                    => 'Connection',
                'contentdisposition'            => 'Content-Disposition',
                'contentencoding'               => 'Content-Encoding',
                'contentlanguage'               => 'Content-Language',
                'contentlength'                 => 'Content-Length',
                'contentlocation'               => 'Content-Location',
                'contentmd5'                    => 'Content-MD5',
                'contentrange'                  => 'Content-Range',
                'contentsecuritypolicy'         => 'Content-Security-Policy',
                'contenttype'                   => 'Content-Type',
                'cookie'                        => 'Cookie',
                'date'                          => 'Date',
                'dnt'                           => 'DNT',
                'etag'                          => 'ETag',
                'expect'                        => 'Expect',
                'expires'                       => 'Expires',
                'forwarded'                     => 'Forwarded',
                'from'                          => 'From',
                'frontendhttps'                 => 'Front-End-Https',
                'host'                          => 'Host',
                'ifmatch'                       => 'If-Match',
                'ifmodifiedsince'               => 'If-Modified-Since',
                'ifnonematch'                   => 'If-None-Match',
                'ifrange'                       => 'If-Range',
                'ifunmodifiedsince'             => 'If-Unmodified-Since',
                'lastmodified'                  => 'Last-Modified',
                'link'                          => 'Link',
                'location'                      => 'Location',
                'maxforwards'                   => 'Max-Forwards',
                'origin'                        => 'Origin',
                'p3p'                           => 'P3P',
                'pragma'                        => 'Pragma',
                'proxyauthenticate'             => 'Proxy-Authenticate',
                'proxyauthorization'            => 'Proxy-Authorization',
                'proxyconnection'               => 'Proxy-Connection',
                'publickeypins'                 => 'Public-Key-Pins',
                'range'                         => 'Range',
                'referer'                       => 'Referer',
                'refresh'                       => 'Refresh',
                'retryafter'                    => 'Retry-After',
                'server'                        => 'Server',
                'setcookie'                     => 'Set-Cookie',
                'status'                        => 'Status',
                'stricttransportsecurity'       => 'Strict-Transport-Security',
                'te'                            => 'TE',
                'tk'                            => 'Tk',
                'trailer'                       => 'Trailer',
                'transferencoding'              => 'Transfer-Encoding',
                'upgrade'                       => 'Upgrade',
                'upgradeinsecurerequests'       => 'Upgrade-Insecure-Requests',
                'useragent'                     => 'User-Agent',
                'vary'                          => 'Vary',
                'via'                           => 'Via',
                'warning'                       => 'Warning',
                'wwwauthenticate'               => 'WWW-Authenticate',
                'xattdeviceid'                  => 'X-ATT-DeviceId',
                'xcontentduration'              => 'X-Content-Duration',
                'xcontentsecuritypolicy'        => 'X-Content-Security-Policy',
                'xcontenttypeoptions'           => 'X-Content-Type-Options',
                'xcorrelationid'                => 'X-Correlation-ID',
                'xcsrftoken'                    => 'X-Csrf-Token',
                'xforwardedfor'                 => 'X-Forwarded-For',
                'xforwardedhost'                => 'X-Forwarded-Host',
                'xforwardedproto'               => 'X-Forwarded-Proto',
                'xframeoptions'                 => 'X-Frame-Options',
                'xhttpmethodoverride'           => 'X-Http-Method-Override',
                'xpoweredby'                    => 'X-Powered-By',
                'xrequestedwith'                => 'X-Requested-With',
                'xrequestid'                    => 'X-Request-ID',
                'xuacompatible'                 => 'X-UA-Compatible',
                'xuidh'                         => 'X-UIDH',
                'xwapprofile'                   => 'X-Wap-Profile',
                'xwebkitcsp'                    => 'X-WebKit-CSP',
                'xxssprotection'                => 'X-XSS-Protection'
            ];
        }
        // if no match is found, we'll fallback to given name
        $res = $name;
        // set name to lowercase and strip dashes
        $key = str_replace('-', '', strtolower($name));
        // look for a match
        if(isset($fields[$key])) {
            $res = $fields[$key];
        }
        return $res;
    }    
}