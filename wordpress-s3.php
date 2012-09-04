<?php
/*
Plugin Name: Amazon S3 for WordPress
Plugin URI: http://tantannoodles.com/toolkit/wordpress-s3/
Description: Allows you to retrieves objects stored in Amazon S3 and post them in WordPress.
Author: Joe Tan
Version: 0.4
Author URI: http://tantannoodles.com/

Copyright (C) 2008 Joe Tan

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA


Release Page:
http://tantannoodles.com/toolkit/wordpress-s3/

Project Page:
http://code.google.com/p/wordpress-s3/

Changlog:
http://code.google.com/p/wordpress-s3/wiki/ChangeLog

$Revision: 89650 $
$Date: 2009-01-27 02:01:33 +0000 (Tue, 27 Jan 2009) $
$Author: joetan $
*/
if (class_exists('TanTanWordPressS3Plugin')) return;

// s3 lib requires php5
if (strpos($_SERVER['REQUEST_URI'], '/wp-admin/') >= 0) { // just load in admin
	$ver = get_bloginfo('version');
    if (version_compare(phpversion(), '5.0', '>=') && version_compare($ver, '2.1', '>=')) {
        require_once(dirname(__FILE__).'/wordpress-s3/class-plugin.php');
        $TanTanWordPressS3Plugin = new TanTanWordPressS3Plugin();
	} elseif (ereg('wordpress-mu-', $ver)) {
        require_once(dirname(__FILE__).'/wordpress-s3/class-plugin.php');
        $TanTanWordPressS3Plugin = new TanTanWordPressS3Plugin();
    } else {
        class TanTanWordPressS3Error {
        function TanTanWordPressS3Error() {add_action('admin_menu', array(&$this, 'addhooks'));}
        function addhooks() {add_options_page('Amazon S3', 'Amazon S3', 10, __FILE__, array(&$this, 'admin'));}
        function admin(){include(dirname(__FILE__).'/wordpress-s3/admin-version-error.html');}
        }
        $error = new TanTanWordPressS3Error();
    }
} else {
    require_once(dirname(__FILE__).'/wordpress-s3/class-plugin-public.php');
    $TanTanWordPressS3Plugin = new TanTanWordPressS3PluginPublic();
}
?>