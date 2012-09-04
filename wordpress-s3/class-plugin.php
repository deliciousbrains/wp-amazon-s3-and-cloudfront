<?php
/*
$Revision: 89650 $
$Date: 2009-01-27 02:01:33 +0000 (Tue, 27 Jan 2009) $
$Author: joetan $
*/
require_once(dirname(__FILE__).'/class-plugin-public.php');
class TanTanWordPressS3Plugin extends TanTanWordPressS3PluginPublic {
    
    function TanTanWordPressS3Plugin() {
		parent::TanTanWordPressS3PluginPublic();
		if (!file_exists(dirname(__FILE__).'/config.php')) {
			add_action('admin_menu', array(&$this, 'settings'));
		}
        if (!$this->options['hideAmazonS3UploadTab']) {
			add_action('load-upload.php', array(&$this, 'addPhotosTab')); // WP < 2.5
			
			// WP >= 2.5
			add_action('media_buttons_context', array(&$this, 'media_buttons')); 
			add_action('media_upload_tantan-wordpress-s3', array(&$this, 'media_upload_content'));
		}
        add_action('activate_tantan/wordpress-s3.php', array(&$this, 'activate'));
        if ($_GET['tantanActivate'] == 'wordpress-s3') {
            $this->showConfigNotice();
        }
        $this->photos = array();
        $this->albums = array();
        $this->perPage = 1000;


    }
    
    // this should install the javascripts onto the user's s3.amazonaws.com account
    
    function installAjax() {
        $js = array('S3Ajax.js');
    }
    
    function activate() {
        wp_redirect('plugins.php?tantanActivate=wordpress-s3');
        exit;
    }
    function deactivate() {}
    
    function showConfigNotice() {
        add_action('admin_notices', create_function('', 'echo \'<div id="message" class="updated fade"><p>Amazon S3 Plugin for WordPress <strong>activated</strong>. <a href="options-general.php?page=tantan/wordpress-s3/class-plugin.php">Configure the plugin &gt;</a></p></div>\';'));
    }

	function settings() {
		add_options_page('Amazon S3', 'Amazon S3', 10, __FILE__, array(&$this, 'admin'));
		$this->version_check();
	}
    function addhooks() {
		parent::addhooks();
        if (!$_POST['disable_amazonS3']) {
            add_filter('wp_update_attachment_metadata', array(&$this, 'wp_update_attachment_metadata'), 9, 2);
			//can't delete mirrored files just yet
			//add_filter('wp_get_attachment_metadata', array(&$this, 'wp_get_attachment_metadata'));
			//add_filter('wp_delete_file', array(&$this, 'wp_delete_file'));
        }
    }  
    function version_check() {
        global $TanTanVersionCheck;
        if (is_object($TanTanVersionCheck)) {
            $data = get_plugin_data(dirname(__FILE__).'/../wordpress-s3.php');
            $TanTanVersionCheck->versionCheck(668, $data['Version']);
        }
    }
    function admin() {
        if ($_POST['action'] == 'save') {
            if (!is_array($_POST['options'])) $_POST['options'] = array();
            $options = get_option('tantan_wordpress_s3');
            
            $_POST['options']['key'] = trim($_POST['options']['key']);
            $_POST['options']['secret'] = trim($_POST['options']['secret']);
            
            if (!$_POST['options']['secret'] || ereg('not shown', $_POST['options']['secret'])) {
                $_POST['options']['secret'] = $options['secret'];
            }
            
            update_option('tantan_wordpress_s3', $_POST['options']);
            
            if ($_POST['options']['bucket']) {
                $options = get_option('tantan_wordpress_s3');
                require_once(dirname(__FILE__).'/lib.s3.php');
                $s3 = new TanTanS3($options['key'], $options['secret']);
            
                if (!in_array($_POST['options']['bucket'], $s3->listBuckets())) {
                    if ($s3->createBucket($_POST['options']['bucket'],'public-read')) {
                        $message = "Saved settings and created a new bucket: ".$_POST['options']['bucket'];
                    } else {
                        $error = "There was an error creating the bucket: ".$_POST['options']['bucket'];
                    }
                } else {
                    $message = "Saved settings.";
                }
            } else {
                $message = "Saved Amazon S3 authentication information. ";
            }
            if (function_exists('dns_get_record') && $_POST['options']['virtual-host']) {
                $record = dns_get_record($_POST['options']['bucket']);
                if (($record[0]['type'] != 'CNAME') || ($record[0]['target'] != $_POST['options']['bucket'].'s3.amazonaws.com')) {
                    $error = "Warning: Your DNS doesn't seem to be setup correctly to virtually host the domain <em>".$_POST['options']['bucket']."</em>. ".
						"Double check and make sure the following entry is added to your DNS. ".
						"<a href='http://docs.amazonwebservices.com/AmazonS3/2006-03-01/VirtualHosting.html'>More info &gt;</a>".
						"<br /><br />".
                        "<code>".$_POST['options']['bucket']." CNAME ".$_POST['options']['bucket'].".s3.amazonaws.com.</code>".
						"<br /><br />".
						"<small>You can ignore this message if you're sure everything is setup correctly.</small>";
                }
            }
        }
        $options = get_option('tantan_wordpress_s3');
        if ($options['key'] && $options['secret']) {
            require_once(dirname(__FILE__).'/lib.s3.php');
            $s3 = new TanTanS3($options['key'], $options['secret']);
            if (!($buckets = $s3->listBuckets())) {
                $error = $this->getErrorMessage($s3->parsed_xml, $s3->responseCode);
            }
            
            $s3->initCacheTables();
            
        } elseif ($options['key']) {
            $error = "Please enter your Secret Access Key.";
        } elseif ($options['secret']) {
            $error = "Please enter your Access Key ID.";
        }
        
        
        include(dirname(__FILE__).'/admin-options.html');
    }
    

	/*
	Delete corresponding file from Amazon S3
	*/
	function wp_delete_file($file) {
		return $file;
		if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
        
        if (!$this->options['wp-uploads'] || !$this->options['bucket'] || !$this->options['secret']) {
            return $file;
        }

		if (is_array($this->meta)) {
			require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
			$this->s3->setOptions($this->options);
			if (deleteObject($this->meta['bucket'], $this->meta['key'])) {
				
			}
			$accessDomain = $this->options['virtual-host'] ? $this->meta['bucket'] : $this->meta['bucket'].'.s3.amazonaws.com';
			return $file;
            //return 'http://'.$accessDomain.'/'.$amazon['key'];
            
		}
		return $file;
	}
	function wp_get_attachment_metadata($data=false, $postID=false) {
		if (is_numeric($postID)) $this->meta = get_post_meta($postID, 'amazonS3_info', true);
		return $data;
	}
    /*
    Handle uploads through default WordPress upload handler
    */
    function wp_update_attachment_metadata($data, $postID) {
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
        
        if (!$this->options['wp-uploads'] || !$this->options['bucket'] || !$this->options['secret']) {
            return $data;
        }
	        
		add_filter('option_siteurl', array(&$this, 'upload_path'));
        $uploadDir = wp_upload_dir();
		remove_filter('option_siteurl', array(&$this, 'upload_path'));
        $parts = parse_url($uploadDir['url']);
        
        $prefix = substr($parts['path'], 1) .'/';
        $type = get_post_mime_type($postID);

       	$data['file'] = get_attached_file($postID, true);

		if (file_exists($data['file'])) {
			$file = array(
                'name' => basename($data['file']),
			    'type' => $type,
			    'tmp_name' => $data['file'],
			    'error' => 0,
			    'size' => filesize($data['file']),
			);
			
			require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
			$this->s3->setOptions($this->options);

			if ($this->s3->putObjectStream($this->options['bucket'], $prefix.$file['name'], $file)) {
			    
			    if ($data['thumb']) {
			        $thumbpath = str_replace( basename( $data['file'] ), $data['thumb'], $data['file'] );
			        $filethumb = array(
                        'name' => $data['thumb'],
                        'type' => $type,
                        'tmp_name' => $thumbpath,
                        'size' => filesize($thumbpath),
			        );
			        
			        $this->s3->putObjectStream($this->options['bucket'], $prefix.$filethumb['name'], $filethumb);
			    } elseif (count($data['sizes'])) foreach ($data['sizes'] as $altName => $altSize) {
					$altPath = str_replace( basename( $data['file'] ), $altSize['file'], $data['file'] );
					$altMeta = array(
						'name' => $altSize['file'],
						'type' => $type,
						'tmp_name' => $altPath,
						'size' => filesize($altPath),
						);
			        $this->s3->putObjectStream($this->options['bucket'], $prefix.$altMeta['name'], $altMeta);
				
				}
			    
			    
			    delete_post_meta($postID, 'amazonS3_info');
                add_post_meta($postID, 'amazonS3_info', array(
                    'bucket' => $this->options['bucket'],
                    'key' => $prefix.$file['name']
                    ));
			} else {
			    
			}
		}
        return $data;
    }
    function wp_handle_upload($info) {
        return $info;
    }

	// figure out the correct path to upload to, for wordpress mu installs
	function upload_path($path='') {
		global $current_blog;
		if (!$current_blog) return $path;
        if ($current_blog->path == '/' && ($current_blog->blog_id != 1)) {
			$dir = substr($current_blog->domain, 0, strpos($current_blog->domain, '.'));
		} else {
		    // prepend a directory onto the path for vhosted blogs
		    if (constant("VHOST") != 'yes') {
		        $dir = '';
		    } else {
		        $dir = $current_blog->path;
		    }
		}
		//echo trim($path.'/'.$dir, '/');
		if ($path == '') {
		    $path = $current_blog->path;
		}
		return trim($path.'/'.$dir, '/');
	}
	function media_buttons($context) {
		global $post_ID, $temp_ID;
		$dir = dirname(__FILE__);
		$pluginRootURL = get_option('siteurl').substr($dir, strpos($dir, '/wp-content'));
		$image_btn = $pluginRootURL.'/database.png';
		$image_title = 'Amazon S3';
		
		$uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);

		$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
		$out = ' <a href="'.$media_upload_iframe_src.'&tab=tantan-wordpress-s3&TB_iframe=true&height=500&width=640" class="thickbox" title="'.$image_title.'"><img src="'.$image_btn.'" alt="'.$image_title.'" /></a>';
		return $context.$out;
	}
	function media_upload_content() {
        $this->upload_files_tantan_amazons3(); // process any uploaded files or new folders
            
		if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
		//if (!is_object($this->s3)) {
	        require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
	        $this->s3->setOptions($this->options);
        //}
        
        add_action('admin_print_scripts', array(&$this, 'upload_tabs_scripts'));
		wp_iframe(array(&$this, 'tab'));
	}
    /*
    Display tabs
    */
    function addPhotosTab() {
        add_filter('wp_upload_tabs', array(&$this, 'wp_upload_tabs'));
        add_action('upload_files_tantan_amazons3', array(&$this, 'upload_files_tantan_amazons3'));
        add_action('upload_files_upload', array(&$this, 'upload_files_upload'));
        add_action('admin_print_scripts', array(&$this, 'upload_tabs_scripts'));
    }
    function wp_upload_tabs ($array) {
    /*
        0 => tab display name, 
        1 => required cap, 
        2 => function that produces tab content, 
        3 => total number objects OR array(total, objects per page), 
        4 => add_query_args
	*/
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
        require_once(dirname(__FILE__).'/lib.s3.php');
        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
        

        if ($this->options['key'] && $this->options['secret'] && $this->options['bucket']) {
            $paged = array();
	        $args = array('prefix' => ''); // this doesn't do anything in WP 2.1.2
            $tab = array(
                'tantan_amazons3' => array('Amazon S3', 'upload_files', array(&$this, 'tab'), $paged, $args),
                //'tantan_amazons3_upload' => array('Upload S3', 'upload_files', array(&$this, 'upload'), $paged, $args),
                );

            return array_merge($array, $tab);
        } else {
            return $array;
        }
    }

    function upload_tabs_scripts() {
        //wp_enqueue_script('prototype');
        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');

        $accessDomain = $this->options['virtual-host'] ? $this->options['bucket'] : $this->options['bucket'].'.s3.amazonaws.com';
        
        include(dirname(__FILE__).'/admin-tab-head.html');
    }
    function upload_files_upload() {
        // javascript here to inject javascript and allow the upload from to post to amazon s3 instead
    }
    function upload_files_tantan_amazons3() {
		global $current_blog;
		$restrictPrefix = ''; // restrict to a selected prefix in current bucket
		if ($current_blog)  { // if wordpress mu
        	$restrictPrefix = ltrim($this->upload_path().'/files/', '/');
		}
	
		if (is_array($_FILES['newfile'])) {
			$file = $_FILES['newfile'];
	        if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
	        require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);
			$this->s3->setOptions($this->options);
			$this->s3->putObjectStream($this->options['bucket'], $restrictPrefix.$_GET['prefix'].$file['name'], $file);
		}
		if ($_POST['newfolder']) {
			if (!$this->options) $this->options = get_option('tantan_wordpress_s3');
	        require_once(dirname(__FILE__).'/lib.s3.php');
	        $this->s3 = new TanTanS3($this->options['key'], $this->options['secret']);

			$this->s3->putPrefix($this->options['bucket'], $restrictPrefix.$_POST['prefix'].$_POST['newfolder']);
		}
    }
    function tab() {
		global $current_blog;
		$restrictPrefix = ''; // restrict to a selected prefix in current bucket
		if ($current_blog)  { // if wordpress mu
        	$restrictPrefix = ltrim($this->upload_path().'/files/', '/');
		}

        $offsetpage = (int) $_GET['paged'];
        if (!$offsetpage) $offsetpage = 1;
        
        if (!$this->options['key'] || !$this->options['secret']) {
            return;
        }
        $bucket = $this->options['bucket'];
        $accessDomain = $this->options['virtual-host'] ? $this->options['bucket'] : $this->options['bucket'].'.s3.amazonaws.com';
        
        $prefix = $_GET['prefix'] ? $_GET['prefix'] : '';
        list($prefixes, $keys, $meta, $privateKeys) = $this->getKeys($restrictPrefix.$prefix);
		if ($restrictPrefix) {
			foreach ($prefixes as $k=>$v) {
				$prefixes[$k] = str_replace($restrictPrefix, '', $v);
			}
		}
        include(dirname(__FILE__).'/admin-tab.html');
    }
    
    function getErrorMessage($parsed_xml, $responseCode){
    	$message = 'Error '.$responseCode.': ' . $parsed_xml->Message;
    	if(isset($parsed_xml->StringToSignBytes)) $message .= "<br>Hex-endcoded string to sign: " . $parsed_xml->StringToSignBytes;
    	return $message;
    }

    // turns array('a', 'b', 'c') into $array['a']['b']['c']
    function mapKey($keys, $path) {
        $k =& $keys;
        $size = count($path) - 1;
        $workingPath = '/';
        foreach ($path as $i => $p) {
            if ($i === $size) {
                $k['_size'] = isset($k['_size']) ? $k['_size'] + 1 : 1;
                $k['_path'] = $workingPath;
                $k['_objects'][$k['_size']] = $p;
            } else {
                $k =& $k[$p]; // traverse the tree
                $workingPath .= $p . '/';
            }
        }
        return $keys;
    }
    
    // should probably figgure out a way to cache these results to make things more speedy
    function getKeys($prefix) {
        $ret = $this->s3->listKeys($this->options['bucket'], false, urlencode($prefix), '/');//, false, 's3/', '/');
        
        if ($this->s3->responseCode >= 400) {
            return array();
        }
        $keys = array();
        $privateKeys = array();
	    $prefixes = array();
	    $meta = array();
	    if ($this->s3->parsed_xml->CommonPrefixes) foreach ($this->s3->parsed_xml->CommonPrefixes as $content) {
	        $prefixes[] = (string) $content->Prefix;
	    }

	    if ($this->s3->parsed_xml->Contents) foreach ($this->s3->parsed_xml->Contents as $content) {
	        $key = (string) $content->Key;
	        if ($this->isPublic($key)) $keys[] = $key;
	        else {
				if (!($p1 = ereg('^\.', $key)) && 
					!($p2 = ereg('_\$folder\$$', $key)) &&
					!($p3 = ereg('placeholder.ns3', $key))) {
					$privateKeys[] = $key;
				} elseif ($p2) {
					$prefix = ereg_replace('(_\$folder\$$)', '/', $key);
					if (!in_array($prefix, $prefixes)) $prefixes[] = $prefix;
				} else {
					
				}
			}
	    }
	    if ($this->options['permissions'] == 'public') {
			foreach ($privateKeys as $key) {
				$this->s3->setObjectACL($this->options['bucket'], $key, 'public-read');
				$keys[] = $key;
			}
		}

	    foreach ($keys as $i => $key) {
	        $meta[$i] = $this->s3->getMetadata($this->options['bucket'], $key);
	    }
		natcasesort($keys);
		natcasesort($prefixes);
	
		return array($prefixes, $keys, $meta, $privateKeys);
    }
    
    function isPublic($key) {
        $everyone = 'http://acs.amazonaws.com/groups/global/AllUsers';
        $this->s3->getObjectACL($this->options['bucket'], $key);
        $acl = (array) $this->s3->parsed_xml->AccessControlList;
        if (is_array($acl['Grant'])) foreach ($acl['Grant'] as $grant) {
            $grant = (array) $grant;
            if ($grant['Grantee'] && (ereg('AllUsers', (string) $grant['Grantee']->URI))) {
                $perm = (string) $grant['Permission'];
                if ($perm == 'READ' || $perm == 'FULL_CONTROL') return true;
            }
        }

        
    }
}
?>