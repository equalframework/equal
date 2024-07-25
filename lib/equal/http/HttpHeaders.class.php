<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\http;


class HttpHeaders {

    // map of headers
    private $headers;

    // map for cookies extra data (for HttpResponse)
    private $cookies_params;

    public function __construct($headers=[]) {
        $this->setHeaders($headers);
        $this->cookies_params = [];
    }

    public function toArray() {
        return $this->getHeaders();
    }

    public function set($header, $value) {
        $header = self::normalizeName($header);
        $this->headers[$header] = $value;
        return $this;
    }

    public function setHeaders($headers=[]) {
        $headers = (array) $headers;
        foreach($headers as $header => $value) {
            $this->set($header, $value);
        }
        return $this;
    }

    public function setCookie($cookie, $value, $params=null) {
        $cookies = $this->getCookies();
        $cookies[$cookie] = $value;
        $cookie_parts = array_map(function ($cookie, $value) { return "$cookie=$value"; }, array_keys($cookies), $cookies);
        $this->set('Cookie', implode('; ', $cookie_parts));
        if($params) {
            $this->cookies_params[$cookie] = $params;
        }
        return $this;
    }

    public function setCharset($charset) {
        if(isset($this->headers['Accept-Charset'])) {
            $this->set('Accept-Charset', $charset);
        }
        else {
            static $map_charset_allowed = [
                    'text/html'                 => true,
                    'text/plain'                => true,
                    'text/css'                  => true,
                    'application/json'          => true,
                    'application/javascript'    => true,
                    'application/xml'           => true,
                    'application/xhtml+xml'     => true
                ];

            $content_type = $this->getContentType();
            if(isset($map_charset_allowed[$content_type])) {
                $this->set('Content-Type', $content_type.'; charset='.strtoupper($charset));
            }
        }
        return $this;
    }

    public function setContentType($content_type) {
        // the header is part of a Request
        if(isset($this->headers['Accept'])) {
            return $this->set('Accept', $content_type);
        }
        // the header is part of a Response
        else {
            $value = $content_type;
            $charset = $this->getCharset();
            if(strlen($charset)) {
                $value .= '; charset='.$charset;
            }
            return $this->set('Content-Type', $value);
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
        if(isset($this->headers[$header])) {
            $res = $this->headers[$header];
        }
        else {
            $normalized_header = self::normalizeName($header);
            if(isset($this->headers[$normalized_header])) {
                $res = $this->headers[$normalized_header];
            }
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

    public function getCookieParams($cookie) {
        $result = null;
        if(isset($this->cookies_params[$cookie])) {
            $result = $this->cookies_params[$cookie];
        }
        return $result;
    }

    public function getCookie($cookie, $default=null)  {
        $cookies = $this->getCookies();
        if(isset($cookies[$cookie])) {
            return $cookies[$cookie];
        }
        return $default;
    }

    // #todo: make explicit distinction between charset used in the message and expected charset in response
    public function getCharsets()  {
        $charsets = [];
        if(isset($this->headers['Accept-Charset'])) {
            // general syntax: character_set [q=value]
            // example: Accept-Charset: iso-8859-5, unicode-1-1; q=0.8
            $parts = explode(',', $this->headers['Accept-Charset']);
            if(count($parts)) {
                $charsets = array_map(function($a) { return trim(explode(';', $a)[0]); }, $parts);
            }
        }
        elseif(isset($this->headers['Content-Type'])) {
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
        return $charsets;
    }

    /**
     * Return the charset currently set in the header, if any.
     *
     */
    public function getCharset() {
        $charsets = $this->getCharsets();
        return count($charsets)?$charsets[0]:'';
    }

    public function getLanguages()  {
        $languages = (array) 'en';
        if(isset($this->headers['Accept-Language'])) {
            // general syntax: language [q=value]
            // example: Accept-Language: da, en-gb;q=0.8, en;q=0.7
            $matches = [];
            if(preg_match_all("/([^-;]*)(?:-([^;]*))?(?:;q=([0-9]\.[0-9]))?/", $this->headers['Accept-Language'], $matches)) {
                if(count($matches)) {
                    $languages = [];
                    foreach($matches[0] as $factor) {
                        if(!strlen($factor)) {
                            continue;
                        }
                        $parts = explode(';q=', $factor);
                        $lang_descr = explode(',', trim($parts[0], ','));
                        foreach($lang_descr as $lang) {
                            $languages[] = $lang;
                        }
                    }
                }
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
     * Returns the IP addresses of the HTTP message.
     * List contains original IP (for) and, if set, a series of IP of used proxies that passed the request.
     *
     * @return array The IP addresses of the HTTP message.
     *
     * @see getIpAddress()
     */
    private function getIpAddresses() {
        $ip_addresses = [];

        if(isset($this->headers['Forwarded'])) {
            preg_match_all('/(for)=("?\[?)([a-z0-9\.:_\-\/]*)/', $this->headers['Forwarded'], $matches);
            foreach($matches as $match) {
                $ip_addresses[] = $match[3];
            }
            preg_match_all('/(by)=("?\[?)([a-z0-9\.:_\-\/]*)/', $this->headers['Forwarded'], $matches);
            foreach($matches as $match) {
                $ip_addresses[] = $match[3];
            }
        }
        elseif(isset($this->headers['X-Forwarded-For'])) {
            $ip_addresses = array_map('trim', explode(',', $this->headers['X-Forwarded-For']));
        }

        foreach($ip_addresses as $key => $client_ip) {
            // remove port, if any
            if (preg_match('{((?:\d+\.){3}\d+)\:\d+}', $client_ip, $match)) {
                $ip_addresses[$key] = $match[1];
            }
            // remove invalid addresses
            if (!filter_var($ip_addresses[$key], FILTER_VALIDATE_IP)) {
                unset($ip_addresses[$key]);
                continue;
            }
        }

        return $ip_addresses;
    }

    /**
     * Returns the original client IP address.
     *
     * @return string The IP address of the client from which the message originates.
     *
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getIpAddress() {
        $ip_addresses = $this->getIpAddresses();
        return count($ip_addresses) ? $ip_addresses[0] : '127.0.0.1';
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
     *
     * List of official and common non-standards fields from Wikipedia
     * @link https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
     */
    public static function normalizeName($name) {
        static $map_types = null;

        if(is_null($map_types)) {
            $map_types = [
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
                'secchua'	                    => 'Sec-CH-UA',
                'secchuamobile'	                => 'Sec-CH-UA-Mobile',
                'secchuaplatform'               => 'Sec-CH-UA-Platform',
                'secfetchsite'                  => 'Sec-Fetch-Site',
                'secfetchmode'                  => 'Sec-Fetch-Mode',
                'secfetchdest'                  => 'Sec-Fetch-Dest',
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
        // by default, fallback to received name
        $res = $name;
        // set name to lowercase and strip dashes
        $key = str_replace('-', '', strtolower($name));
        // look for a match
        if(isset($map_types[$key])) {
            $res = $map_types[$key];
        }
        return $res;
    }
}