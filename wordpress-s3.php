<?php
/*
Plugin Name: Amazon S3 and CloudFront
Plugin URI: http://wordpress.org/extend/plugins/amazon-s3-and-cloudfront/
Description: Automatically copies media uploads to Amazon S3 for storage and delivery. Optionally configure Amazon CloudFront for even faster delivery.
Author: Brad Touesnard
Version: 0.6.1
Author URI: http://bradt.ca

// Copyright (c) 2013 Brad Touesnard. All rights reserved.
//
// Released under the GPL license
// http://www.opensource.org/licenses/gpl-license.php
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************
//
// Forked Amazon S3 for WordPress with CloudFront (http://wordpress.org/extend/plugins/tantan-s3-cloudfront/)
// which is a fork of Amazon S3 for WordPress (http://wordpress.org/extend/plugins/tantan-s3/).
// Then completely rewritten.
*/

function as3cf_check_required_plugin() {
    if ( class_exists( 'Amazon_Web_Services' ) || !is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        return;
    }

    require_once ABSPATH . '/wp-admin/includes/plugin.php';
    deactivate_plugins( __FILE__ );

    $msg = sprintf( __( 'Amazon S3 and CloudFront has been deactivated as it requires the <a href="%s">Amazon&nbsp;Web&nbsp;Services</a> plugin.', 'as3cf' ), 'https://github.com/deliciousbrains/wp-amazon-web-services' ) . '<br /><br />';
    
    if ( file_exists( WP_PLUGIN_DIR . '/amazon-web-services/amazon-web-services.php' ) ) {
        $activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=amazon-web-services/amazon-web-services.php', 'activate-plugin_amazon-web-services/amazon-web-services.php' );
        $msg .= sprintf( __( 'It appears to already be installed. <a href="%s">Click here to activate it.</a>', 'as3cf' ), $activate_url );
    }
    else {
        $download_url = 'https://github.com/deliciousbrains/wp-amazon-web-services/releases/download/v0.1/amazon-web-services-0.1.zip';
        $msg .= sprintf( __( '<a href="%s">Click here to download a zip of the latest version.</a> Then install and activate it. ', 'as3cf' ), $download_url );
    }

    $msg .= '<br /><br />' . __( 'Once it has been activated, you can activate Amazon&nbsp;S3&nbsp;and&nbsp;CloudFront.', 'as3cf' );

    wp_die( $msg );
}

add_action( 'plugins_loaded', 'as3cf_check_required_plugin' );

function as3cf_init( $aws ) {
    global $as3cf;
    require_once 'classes/amazon-s3-and-cloudfront.php';
    $as3cf = new Amazon_S3_And_CloudFront( __FILE__, $aws );
}

add_action( 'aws_init', 'as3cf_init' );