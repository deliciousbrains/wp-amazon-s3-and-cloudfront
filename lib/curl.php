<?php
/*
This is a clone of the PEAR HTTP/Request class object. It uses libcurl to do the networking stuff. 
Should also work with the HTTPS protocol

Important: Not every method has been ported, just the ones that were needed.

$Revision: 66280 $
$Date: 2008-09-25 15:28:23 +0000 (Thu, 25 Sep 2008) $
$URL: https://photo-album.googlecode.com/svn/trunk/tantan-flickr/lib/curl.php $
*/

class TanTanHTTPRequestCurl {
    var $curl;
    var $postData;
    var $cookies;
    var $raw;
    var $response;
    var $headers;
    var $url;
    
    function TanTanHTTPRequestCurl($url = '', $params = array()) {
        $this->curl = curl_init();
        $this->postData = array();
        $this->cookies = array();
        $this->headers = array();
        if (!empty($url)) { 
            $this->setURL($url);
        } else { 
            $this->setURL(false); 
        }
        foreach ($params as $key => $value) {
            $this->{'_' . $key} = $value;
        }
        
        $this->addHeader('Connection', 'close');
        
        // We don't do keep-alives by default
        $this->addHeader('Connection', 'close');

        // Basic authentication
        if (!empty($this->_user)) {
            $this->addHeader('Authorization', 'Basic ' . base64_encode($this->_user . ':' . $this->_pass));
        }

        // Proxy authentication (see bug #5913)
        if (!empty($this->_proxy_user)) {
            $this->addHeader('Proxy-Authorization', 'Basic ' . base64_encode($this->_proxy_user . ':' . $this->_proxy_pass));
        }

    }
    
    function addHeader($header, $value) {
        $this->headers[$header] = $value;
    }
    
    function setMethod($method) {
        switch ($method) {
            case 'DELETE':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
            case HTTP_REQUEST_METHOD_PUT:
            case 'PUT':
                curl_setopt($this->curl, CURLOPT_PUT, true);
                //CURLOPT_INFILE CURLOPT_INFILESIZE
            break;
            case HTTP_REQUEST_METHOD_POST:
            case 'POST':
                curl_setopt($this->curl, CURLOPT_POST, true);
            break;
            default:
            case 'GET':
                curl_setopt($this->curl, CURLOPT_HTTPGET, true);
            break;
            case 'HEAD':
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
            break;
        }
    }
    function setURL($url) {
        $this->url = $url;
    }
    function addPostData($name, $value) {
        $this->postData[$name] = $value;
    }
    function addCookie($name, $value) {
        $this->cookies[$name] = array('name' => $name, 'value' => $value);
    }
    function sendRequest() {
        $headers = array(
           "Accept: *.*",
        );
        
        foreach ($this->headers as $k=>$h) {
            $headers[] = "$k: $h";
        }

        if (count($this->cookies) > 0) {
            $cookieVars = '';
            foreach ($this->cookies as $cookie) {
                //$headers[] = "Cookie: ".$cookie['name'].'='.$cookie['value'];
                $cookieVars .= ''.$cookie['name'].'='.$cookie['value'].'; ';
            }
            curl_setopt($this->curl, CURLOPT_COOKIE, $cookieVars);
            //print_r($cookieVars);
        }
        
        if (count($this->postData) > 0) { // if a POST
            $postVars = '';
            foreach ($this->postData as $key=>$value) {
                $postVars .= $key.'='.urlencode($value).'&';
            }
            // *** TODO ***
            // weird, libcurl doesnt seem to POST correctly
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postVars);

            //curl_setopt($this->curl, CURLOPT_HTTPGET, true);
            //$this->url .= '?'.$postVars;


        } else {
            curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        }
        curl_setopt($this->curl, CURLOPT_URL, $this->url);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        $this->raw = curl_exec($this->curl);
        $this->response = $this->_parseResponse($this->raw);
        return true; // hmm no error checking for now
    }
    
    function getResponseHeader($header=false) {
        if ($header) {
            return $this->response['header'][$header];
        } else {
            return $this->response['header'];
        }
    }
    function getResponseCookies() {
        $hdrCookies = array();
        foreach ($this->response['header'] as $key => $value) {
            if (strtolower($key) == 'set-cookie') {
                $hdrCookies = array_merge($hdrCookies, explode("\n", $value));
            }
        }
        //$hdrCookies = explode("\n", $this->response['header']['Set-Cookie']);
        $cookies = array();
        
        foreach ($hdrCookies as $cookie) {
            if ($cookie) {
                list($name, $value) = explode('=', $cookie, 2);
                list($value, $domain, $path, $expires) = explode(';', $value);
                $cookies[$name] = array('name' => $name, 'value' => $value);
            }
        }
        return $cookies;
    }
    function getResponseBody() {
        return $this->response['body'];
    }
    function getResponseCode() {
        return $this->response['code'];
    }
    function getResponseRaw() {
        return $this->raw;
    }
    function clearPostData($var=false) {
        if (!$var) {
            $this->postData = array();
        } else {
            unset($this->postData[$var]);
        }
    }
    
    function _parseResponse($this_response) {
        if (substr_count($this_response, 'HTTP/1.') > 1) { // yet another weird bug. CURL seems to be appending response bodies together
            $chunks = preg_split('@(HTTP/[0-9]\.[0-9] [0-9]{3}.*\n)@', $this_response, -1, PREG_SPLIT_DELIM_CAPTURE);
            $this_response = array_pop($chunks);
            $this_response = array_pop($chunks) . $this_response;

        }
        
        list($response_headers, $response_body) = explode("\r\n\r\n", $this_response, 2);
        $response_header_lines = explode("\r\n", $response_headers);

        $http_response_line = array_shift($response_header_lines);
        if (preg_match('@^HTTP/[0-9]\.[0-9] 100@',$http_response_line, $matches)) { 
            return $this->_parseResponse($response_body); 
        } else if(preg_match('@^HTTP/[0-9]\.[0-9] ([0-9]{3})@',$http_response_line, $matches)) { 
            $response_code = $matches[1]; 
        }
        $response_header_array = array();
        foreach($response_header_lines as $header_line) {
            list($header,$value) = explode(': ', $header_line, 2);
            $response_header_array[$header] .= $value."\n";
        }
        return array("code" => $response_code, "header" => $response_header_array, "body" => $response_body); 
    }
}
?>