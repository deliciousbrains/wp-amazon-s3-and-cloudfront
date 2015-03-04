# Amazon S3 and Cloudfront
Contributors: bradt
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5VPMGLLK94XJC
Tags: uploads, amazon, s3, mirror, admin, media, cdn, cloudfront
Requires at least: 3.5
Tested up to: 4.1
Stable tag: 0.8.2
License: GPLv3

Copies files to Amazon S3 as they are uploaded to the Media Library. Optionally configure Amazon CloudFront for faster delivery.

## Description

This plugin automatically copies images, videos, documents, and any other media added through WordPress' media uploader to [Amazon Simple Storage Service](http://aws.amazon.com/s3/) (S3). It then automatically replaces the URL to each media file with their respective S3 URL or, if you have configured [Amazon CloudFront](http://aws.amazon.com/cloudfront/), the respective CloudFront URL. Image thumbnails are also copied to S3 and delivered through S3/CloudFront.

Uploading files *directly* to your S3 account is not currently supported by this plugin. They are uploaded to your server first, then copied to S3. There is an option to automatically remove the files from your server once they are copied to S3 however.

If you're adding this plugin to a site that's been around for a while, your existing media files will not be copied or served from S3. Only newly uploaded files will be copied and served from S3.

## Installation

1. Install the required [Amazon Web Services plugin](http://wordpress.org/extend/plugins/amazon-web-services/) using WordPress' built-in installer
2. Follow the instructions to setup your AWS access keys
3. Install this plugin using WordPress' built-in installer
4. Access the *S3 and CloudFront* option under *AWS* and configure

### Screenshots

1. Choosing/creating a bucket
2. Settings screen

### Requirements

= 0.6 =
This version requires PHP 5.3.3+ and the Amazon Web Services plugin

= 0.6.1 =
This version requires PHP 5.3.3+ and the Amazon Web Services plugin

= 0.6.2 =
This version requires PHP 5.3.3+ and the Amazon Web Services plugin

## Pro Version

We’re working on a pro version that will include the following features:

* Copy existing Media Library to S3
* Serve theme JS & CSS from S3/CloudFront
* WooCommerce & EDD integration
* Awesome email support

[Sign up for news about the pro version](https://confirmsubscription.com/h/t/295CA85AEB94E879)

[Request features, report bugs, and submit pull requests on Github](https://github.com/deliciousbrains/wp-amazon-s3-and-cloudfront/issues)

*This plugin has been completely rewritten, but was originally a fork of
[Amazon S3 for WordPress with CloudFront](http://wordpress.org/extend/plugins/tantan-s3-cloudfront/)
which is a fork of [Amazon S3 for WordPress](http://wordpress.org/extend/plugins/tantan-s3/), also known as tantan-s3.*
