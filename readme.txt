=== Amazon S3 and Cloudfront ===
Contributors: bradt
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5VPMGLLK94XJC
Tags: uploads, amazon, s3, mirror, admin, media, cdn, cloudfront
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: 0.6.1
License: GPLv3

Copies files to Amazon S3 as they are uploaded to the Media Library. Optionally configure Amazon CloudFront for faster delivery.

== Description ==

This plugin automatically copies images, videos, documents, and any other media added through WordPress' media uploader to [Amazon Simple Storage Service](http://aws.amazon.com/s3/) (S3). It then automatically replaces the URL to each media file with their respective S3 URL or, if you have configured [Amazon CloudFront](http://aws.amazon.com/cloudfront/), the respective CloudFront URL. Image thumbnails are also copied to S3 and delivered through S3/CloudFront.

Uploading files *directly* to your S3 account is not currently supported by this plugin. They are uploaded to your server first, then copied to S3. There is an option to automatically remove the files from your server once they are copied to S3 however.

If you're adding this plugin to a site that's been around for a while, your existing media files will not be copied or served from S3. Only newly uploaded files will be copied and served from S3.

**[Request features, report bugs, and submit pull requests on Github](https://github.com/bradt/wp-tantan-s3/issues)**

*This plugin has been completely rewritten, but was originally a fork of 
[Amazon S3 for WordPress with CloudFront](http://wordpress.org/extend/plugins/tantan-s3-cloudfront/) 
which is a fork of [Amazon S3 for WordPress](http://wordpress.org/extend/plugins/tantan-s3/), also known as tantan-s3.*

== Installation ==

1. Install the required [Amazon Web Services plugin](https://github.com/deliciousbrains/wp-amazon-web-services) using WordPress' built-in installer
2. Follow the instructions to setup your AWS access keys
3. Install this plugin using WordPress' built-in installer
4. Access the *S3 and CloudFront* option under *AWS* and configure

== Screenshots ==

1. Settings screen

== Upgrade Notice ==

= 0.6 =
This version requires PHP 5.3.3+ and the Amazon Web Services plugin

= 0.6.1 =
This version requires PHP 5.3.3+ and the Amazon Web Services plugin

== Changelog ==

= 0.6.1 - 2013-09-21 =
* WP.org download of Amazon Web Services plugin is giving a 404 Not Found, so directing people to download from Github instead

= 0.6 - 2013-09-20 =
* Complete rewrite
* Now requires PHP 5.3.3+
* Now requires the [Amazon Web Services plugin](https://github.com/deliciousbrains/wp-amazon-web-services) which contains the latest PHP libraries from Amazon
* Now works with multisite
* New Option: Custom S3 object path
* New Option: Always serve files over https (SSL)
* New Option: Enable object versioning by appending a timestamp to the S3 file path
* New Option: Remove uploaded file from local filesystem once it has been copied to S3
* New Option: Copy any HiDPI (@2x) images to S3 (works with WP Retina 2x plugin)

= 0.5 - 2013-01-29 =
* Forked [Amazon S3 for WordPress with CloudFront](http://wordpress.org/extend/plugins/tantan-s3-cloudfront/)
* Cleaned up the UI to fit with today's WP UI
* Fixed issues causing error messages when WP_DEBUG is on
* [Delete files on S3 when deleting WP attachment](https://github.com/bradt/wp-tantan-s3/commit/e777cd49a4b6999f999bd969241fb24cbbcece60)
* [Added filter to the get_attachment_url function](https://github.com/bradt/wp-tantan-s3/commit/bbe1aed5c2ae900e9ba1b16ba6806c28ab8e2f1c)
* [Added function to get a temporary, secure download URL for private files](https://github.com/bradt/wp-tantan-s3/commit/11f46ec2714d34907009e37ad3b97f4421aefed3)
