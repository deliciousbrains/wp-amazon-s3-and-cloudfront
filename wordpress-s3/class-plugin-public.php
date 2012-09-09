<?php
class TanTanWordPressS3PluginPublic {
    var $options;
    var $s3;
	var $meta;

	function TanTanWordPressS3PluginPublic() {
		$this->options = array();
		if (file_exists(dirname(__FILE__).'/config.php')) {
			require_once(dirname(__FILE__).'/config.php');
			if ($TanTanWordPressS3Config) $this->options = $TanTanWordPressS3Config;
		}
		add_action('plugins_loaded', array(&$this, 'addhooks'));
	}
    function addhooks() {
		add_filter('wp_get_attachment_url', array(&$this, 'wp_get_attachment_url'), 9, 2);
	}
	function wp_get_attachment_url($url, $postID) {
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
        
        if ($this->options['wp-uploads'] && ($amazon = get_post_meta($postID, 'amazonS3_info', true))) {
	        if ( isset($this->options['cloudfront']) && $this->options['cloudfront'] ) {
	        	$accessDomain = $this->options['cloudfront'];
			}
	    	elseif ( isset($this->options['virtual-host']) && $this->options['virtual-host'] ) {
	    		$accessDomain = $this->options['bucket'];
	    	}
	        else {
				$accessDomain = $amazon['bucket'] . '.s3.amazonaws.com';
	        }

	        $url = 'http://'.$accessDomain.'/'.$amazon['key'];

	        $url = apply_filters( 'wps3_get_attachment_url', $url, $postID, $this );
	    }

        return $url;
    }


	/**
	* Generate a link to download a file from Amazon S3 using query string
	* authentication. This link is only valid for a limited amount of time.
	*
	* @param $bucket The name of the bucket in which the file is stored.
	* @param $filekey The key of the file, excluding the leading slash.
	* @param $expires The amount of time the link is valid (in seconds).
	* @param $operation The type of HTTP operation. Either GET or HEAD.
	*/
	function get_secure_attachment_url($postID, $expires = 900, $operation = 'GET') {
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');

        if (
			!$this->options['wp-uploads'] || !$this->options['key'] || !$this->options['secret']
			|| !$this->options['bucket'] || !($amazon = get_post_meta($postID, 'amazonS3_info', true))
		) {
			return false;
		}

		$accessDomain = $this->options['virtual-host'] ? $amazon['bucket'] : $amazon['bucket'].'.s3.amazonaws.com';
		
		$expire_time = time() + $expires;
		$filekey = rawurlencode($amazon['key']);
		$filekey = str_replace('%2F', '/', $filekey);
		$path = $amazon['bucket'] .'/'. $filekey;

		/**
		* StringToSign = HTTP-VERB + "\n" +
		* Content-MD5 + "\n" +
		* Content-Type + "\n" +
		* Expires + "\n" +
		* CanonicalizedAmzHeaders +
		* CanonicalizedResource;
		*/
		
		$stringtosign =
			$operation ."\n". // type of HTTP request (GET/HEAD)
			"\n". // Content-MD5 is meaningless for GET
			"\n". // Content-Type is meaningless for GET
			$expire_time ."\n". // set the expire date of this link
			"/$path"; // full path (incl bucket), starting with a /

		require_once(dirname(__FILE__).'/lib.s3.php');
		$s3 = new TanTanS3($this->options['key'], $this->options['secret']);
		$signature = urlencode($s3->constructSig($stringtosign));
		
		return sprintf('http://%s/%s?AWSAccessKeyId=%s&Expires=%u&Signature=%s', $accessDomain, $filekey, $this->options['key'], $expire_time, $signature);
	}
}

function wps3_get_secure_attachment_url($postID, $expires = 900, $operation = 'GET') {
	global $TanTanWordPressS3Plugin;
	return $TanTanWordPressS3Plugin->get_secure_attachment_url($postID, $expires, $operation);
}
