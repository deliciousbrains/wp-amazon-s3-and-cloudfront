<?php
/*
Calculates RFC 2104 compliant HMACs.
Based on code from  http://pear.php.net/package/Crypt_HMAC
*/   
class TanTanCrypt_HMAC {
var $_func;var $_ipad;var $_opad;var $_pack;
function TanTanCrypt_HMAC($key, $func = 'md5'){$this->setFunction($func);$this->setKey($key);}
function setFunction($func){if (!$this->_pack = $this->_getPackFormat($func)) { die('Unsupported hash function'); }$this->_func = $func;}
function setKey($key){$func = $this->_func;if (strlen($key) > 64) {$key =  pack($this->_pack, $func($key));}if (strlen($key) < 64) {$key = str_pad($key, 64, chr(0));}$this->_ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));$this->_opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));}
function _getPackFormat($func){$packs = array('md5' => 'H32', 'sha1' => 'H40');return isset($packs[$func]) ? $packs[$func] : false;}
function hash($data){$func = $this->_func;return $func($this->_opad . pack($this->_pack, $func($this->_ipad . $data)));}
}
/*
class Stream{
var $data;
function stream_function($handle, $fd, $length){return fread($this->data, $length);}
}
*/
if (!class_exists('TanTanHTTPRequestCurl')) require_once (dirname(__FILE__).'/../lib/curl.php');

/*
    based on code provided by Amazon
*/
// This software code is made available "AS IS" without warranties of any
// kind.  You may copy, display, modify and redistribute the software
// code either by itself or as incorporated into your code; provided that
// you do not remove any proprietary notices.  Your use of this software
// code is at your own risk and you waive any claim against Amazon
// Digital Services, Inc. or its affiliates with respect to your use of
// this software code. (c) 2006 Amazon Digital Services, Inc. or its
// affiliates.

class TanTanS3 {

	var $serviceUrl;
	var $accessKeyId;
	var $secretKey;
	var $responseString;
	var $responseCode;
	var $parsed_xml;
	var $req;
	var $fp;
	var $options;
			
	/**
	 * Constructor
	 *
	 * Takes ($accessKeyId, $secretKey, $serviceUrl)
	 *
	 * - [str] $accessKeyId: Your AWS Access Key Id
	 * - [str] $secretKey: Your AWS Secret Access Key
	 * - [str] $serviceUrl: OPTIONAL: defaults: http://s3.amazonaws.com/
	 *
	*/
	function TanTanS3($accessKeyId, $secretKey, $serviceUrl="http://s3.amazonaws.com/") {
		global $wpdb;
		$this->serviceUrl=$serviceUrl;
		$this->accessKeyId=$accessKeyId;
		$this->secretKey=$secretKey;
		$this->req =& new TanTanHTTPRequestCurl($this->serviceUrl);
		$this->options = array();
		$this->options['cache_table'] = $wpdb->prefix . 'tantan_wordpress_s3_cache';
		//$this->req = new HTTP_Request($this->serviceUrl);
	}
			
	function setOptions($options) {
		if (is_array($options)) {
			$this->options = array_merge($this->options, $options);
		}
	}
	/**
	 * listBuckets -- Lists all buckets.
	*/
	function listBuckets() {
		$ret = $this->send('', '');
		if($ret == 200){ 
		    $return = array();
			if(count($this->parsed_xml->Buckets->Bucket) > 0){
			    foreach ($this->parsed_xml->Buckets->Bucket as $bucket) {
			        $return[] = (string) $bucket->Name;
			    }
			}
		    return $return;
			
		}
		else{
			return false;
		}    
	}	
	/**
	 * listKeys -- Lists keys in a bucket.
	 *
	 * Takes ($bucket [,$marker][,$prefix][,$delimiter][,$maxKeys]) -- $marker, $prefix, $delimeter, $maxKeys are independently optional
	 *
	 * - [str] $bucket: the bucket whose keys are to be listed
	 * - [str] $marker: keys returned will occur lexicographically after $marker (OPTIONAL: defaults to false)
	 * - [str] $prefix: keys returned will start with $prefix (OPTIONAL: defaults to false)
	 * - [str] $delimiter: keys returned will be of the form "$prefix[some string]$delimeter" (OPTIONAL: defaults to false)
	 * - [str] $maxKeys: number of keys to be returned (OPTIONAL: defaults to 1000 - maximum allowed by service)
	*/
	function listKeys($bucket, $marker=FALSE, $prefix=FALSE, $delimiter=FALSE, $maxKeys='1000') {
		$ret = $this->send($bucket, '/', "max-keys={$maxKeys}&marker={$marker}&prefix={$prefix}&delimiter={$delimiter}");
		if($ret == 200){
		    return true;
		} else {
			return false;
		}
	}
	function createBucket($bucket, $acl = 'private') {
		$httpDate = gmdate("D, d M Y G:i:s T");
		$stringToSign = "PUT\n\n\n$httpDate\nx-amz-acl:$acl\n/$bucket";
		$signature = $this->constructSig($stringToSign);
		//$req =& new HTTP_Request($this->serviceUrl . $bucket);
		$this->req->setURL($this->serviceUrl . $bucket);
		$this->req->setMethod("PUT");
		$this->req->addHeader("Date", $httpDate);
		$this->req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$this->req->addHeader("x-amz-acl", $acl);
		$this->req->sendRequest();
		$this->responseCode=$this->req->getResponseCode();
		$this->responseString = $this->req->getResponseBody();
		$this->parsed_xml = simplexml_load_string($this->responseString);
		if ($this->responseCode == 200) {
			return true;
		} else {
			return false;
		}
	}	
	/**
	 * getBucketACL -- Gets bucket access control policy.
	 *
	 * Takes ($bucket)
	 *
	 * - [str] $bucket: the bucket whose acl you want
	*/	 
	function getBucketACL($bucket){
		$ret = $this->send($bucket, '/?acl');
		if ($ret == 200) {
			return true;
		} else {
			return false;		
		}
	}
	
	/**
	 * getObjectACL -- gets an objects access control policy.
	 *
	 * Takes ($bucket, $key)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	*/   
	function getObjectACL($bucket, $key){
		$ret = $this->send($bucket, "/".urlencode($key).'?acl');
		if ($ret == 200) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * setObjectACL -- sets objects access control policy to one of Amazon S3 canned policies.
	 *
	 * Takes ($bucket, $key, $acl)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	 * - [str] $acl -- One of canned access control policies.
	*/   
	function setObjectACL($bucket, $key, $acl){
		$serviceUrl = 'http://'.$bucket.'.s3.amazonaws.com/';
		
		$httpDate = gmdate("D, d M Y G:i:s T");
		$resource = urlencode($key);
		$stringToSign = "PUT\n\n\n$httpDate\nx-amz-acl:$acl\n/$bucket/$resource?acl";
		$signature = $this->constructSig($stringToSign);
		//$req =& new HTTP_Request($this->serviceUrl.$resource.'?acl');
		$this->req->setURL($serviceUrl.$resource.'?acl');
		$this->req->setMethod("PUT");
		$this->req->addHeader("Date", $httpDate);
		$this->req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		$this->req->addHeader("x-amz-acl", $acl);
		$this->req->sendRequest();
		if ($this->req->getResponseCode() == 200) {
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * getMetadata -- Gets the metadata associated with an object.
	 *
	 * Takes ($bucket, $key)  
	 *
	 * - [str] $bucket
	 * - [str] $key
	*/   
	function getMetadata($bucket, $key){
	    if ($data = $this->getCache($bucket."/".$key)) {
	        return $data;
	    }
		$ret = $this->send($bucket, "/".urlencode($key), '', 'HEAD');
		if ($ret == 200) {
			$data = $this->req->getResponseHeader();
			foreach ($data as $k => $d) $data[strtolower($k)] = trim($d);
			$this->setCache($bucket."/".$key, $data);
			return $data;
		} else {
			return array();
		}
	}
	
	
    /**
     * putObjectStream -- Streams data to a bucket.
     *
     * Takes ($bucket, $key, $streamFunction, $contentType, $contentLength [,$acl, $metadataArray, $md5])
     * http://www.missiondata.com/blog/linux/49/s3-streaming-with-php/
     *
     * - [str] $bucket: the bucket into which file will be written
     * - [str] $key: key of written file
     * - [str] $fileName: path to file
     * - [str] $contentType: file content type
     * - [str] $contentLength: file content length
     * - [str] $acl: access control policy of file (OPTIONAL: defaults to 'private')
     * - [str] $metadataArray: associative array containing user-defined metadata (name=>value) (OPTIONAL)
     * - [bool] $md5: the MD5 hash of the object (OPTIONAL)
    */
    function putObjectStream($bucket, $key, $fileInfo, $acl='public-read', $metadataArray=array(), $md5=false){
		$serviceUrl = 'http://'.$bucket.'.s3.amazonaws.com/';

        sort($metadataArray);
		$fileName = $fileInfo['tmp_name'];
		$contentLength = $fileInfo['size'];
		$contentType = $fileInfo['type'];
		if (!file_exists($fileName)) {
			return false;
		}
		$this->fp = fopen($fileName, 'r');
        $resource = urlencode($key);
        $httpDate = gmdate("D, d M Y G:i:s T");

        $curl_inst = curl_init();

        curl_setopt ($curl_inst, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt ($curl_inst, CURLOPT_LOW_SPEED_LIMIT, 1);
        curl_setopt ($curl_inst, CURLOPT_LOW_SPEED_TIME, 180);
        curl_setopt ($curl_inst, CURLOPT_NOSIGNAL, 1);
        curl_setopt ($curl_inst, CURLOPT_READFUNCTION, array(&$this, 'stream_function'));
        curl_setopt ($curl_inst, CURLOPT_URL, $serviceUrl . $resource);
        curl_setopt ($curl_inst, CURLOPT_UPLOAD, true);
        curl_setopt ($curl_inst, CURLINFO_CONTENT_LENGTH_UPLOAD, $contentLength);

        $header[] = "Date: $httpDate";
        $header[] = "Content-Type: $contentType";
        $header[] = "Content-Length: $contentLength";
        $header[] = "Expect: ";
		if (is_numeric($this->options['expires'])) {
			$header[] = "Expires: ".date('D, d M Y H:i:s O', time()+$this->options['expires']);
		}
        $header[] = "Transfer-Encoding: ";
        $header[] = "x-amz-acl: $acl";

        $MD5 = "";
        if($md5){
                $MD5 = $this->hex2b64(md5_file($filePath));
                $header[] = "Content-MD5: $MD5";
        }

        $stringToSign="PUT\n$MD5\n$contentType\n$httpDate\nx-amz-acl:$acl\n";
        foreach($metadataArray as $current){
                if($current!=""){
                        $stringToSign.="x-amz-meta-$currentn";
                        $header = substr($current,0,strpos($current,':'));
                        $meta = substr($current,strpos($current,':')+1,strlen($current));
                        $header[] = "x-amz-meta-$header: $meta";
                }
        }

        $stringToSign.="/$bucket/$resource";

        $signature = $this->constructSig($stringToSign);

        $header[] = "Authorization: AWS $this->accessKeyId:$signature";

        curl_setopt($curl_inst, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl_inst, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec ($curl_inst);

        $this->responseString = $result;
        $this->responseCode = curl_getinfo($curl_inst, CURLINFO_HTTP_CODE);

		fclose($this->fp);
        curl_close($curl_inst);
		return true;
    }
	function stream_function($handle, $fd, $length){return fread($this->fp, $length);}

	function putPrefix($bucket, $prefix){
		$ret = $this->send($bucket, "/".urlencode($prefix.'_$folder$'), '', 'PUT', array('Content-Type' => '', 'Content-Length' => 0));
		if ($ret == 200) {
			return true;
		} else {
			return false;
		}
	}
	
	function deleteObject($bucket, $key) {
		$ret = $this->send($bucket, "/".urlencode($key), '', 'DELETE');
		if ($ret == 204) {
			return true;
		} else {
			return false;
		}
	}
		
	function send($bucket, $resource, $args='', $method='GET', $headers=false) {
		if ($bucket != '') {
			$serviceUrl = 'http://'.$bucket.'.s3.amazonaws.com';
		} else {
			$serviceUrl = 'http://s3.amazonaws.com/';
		}

		$method=strtoupper($method);
		$httpDate = gmdate("D, d M Y G:i:s T");
		$signature = $this->constructSig("$method\n\n\n$httpDate\n/".($bucket ? ($bucket.$resource) : $resource));
		
		$this->req->setURL($serviceUrl.$resource.($args ? '?'.$args : ''));
		$this->req->setMethod($method);
		$this->req->addHeader("Date", $httpDate);
		$this->req->addHeader("Authorization", "AWS " . $this->accessKeyId . ":" . $signature);
		if (is_array($headers)) foreach ($headers as $key => $header) $this->req->addHeader($key, $header);
		$this->req->sendRequest();
		if ($method=='GET') {
			$this->parsed_xml = simplexml_load_string($this->req->getResponseBody());
		}

		return $this->req->getResponseCode();
	}
	function hex2b64($str) {
		$raw = '';
		for ($i=0; $i < strlen($str); $i+=2) {
			$raw .= chr(hexdec(substr($str, $i, 2)));
		}
		return base64_encode($raw);
	}
		 
	function constructSig($str) {
		$hasher =& new TanTanCrypt_HMAC($this->secretKey, "sha1");
		$signature = $this->hex2b64($hasher->hash($str));
		return($signature);
	}
	
    function initCacheTables() {
        global $wpdb;
        if (!is_object($wpdb)) return;
        
        $wpdb->query("CREATE TABLE IF NOT EXISTS `".$this->options['cache_table']."` (
                `request` VARCHAR( 255 ) NOT NULL ,
                `response` TEXT NOT NULL ,
                `timestamp` DATETIME NOT NULL ,
                PRIMARY KEY ( `request` )
            ) TYPE = MYISAM");	    
	}
	function setCache($key, $data) {
        global $wpdb;
        if (!is_object($wpdb)) return false;
        $key = addslashes(trim($key));
        if ($wpdb->query("DELETE FROM ".$this->options['cache_table']." WHERE request = '".$key."'") !== false) {
	        $sql = "INSERT INTO ".$this->options['cache_table']." (request, response, timestamp) VALUES ('".$key."', '" . addslashes(serialize($data)) . "', '" . strftime("%Y-%m-%d %H:%M:%S") . "')";
	        $wpdb->query($sql); 
		} else { // tables might not be setup, so just try to do that
			$this->initCacheTables();
		}
        return $data;
	}
	function getCache($key) {
        global $wpdb;
        if (!is_object($wpdb)) return false;
        $key = trim($key);
        $result = @$wpdb->get_var("SELECT response FROM ".$this->options['cache_table']." WHERE request = '" . $key . "' LIMIT 1");
        
        if (!empty($result)) {
            return unserialize($result);
        }
        return false;        
	}
	function clearCache() {
        global $wpdb;
        if (!is_object($wpdb)) return false;
	    $result = @$wpdb->query("DELETE FROM ".$this->options['cache_table'].";");
	}
}
?>