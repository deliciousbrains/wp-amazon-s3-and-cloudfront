=== Amazon S3 and Cloudfront ===
Contributors: bradt
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5VPMGLLK94XJC
Tags: uploads, amazon, s3, mirror, admin, media, cdn, cloudfront
Requires at least: 2.3
Tested up to: 3.5.1
Stable tag: 0.5
License: GPLv3

Automatically copies media uploads to Amazon S3 for storage and delivery. Optionally configure Amazon CloudFront for even faster delivery.

== Description ==

This plugin automatically copies images, videos, documents, and any other media added through WordPress' media uploader to [Amazon Simple Storage Service](http://aws.amazon.com/s3/) (S3). It then automatically replaces the URL to each media file with their respective S3 URL or, if you have configured [Amazon CloudFront](http://aws.amazon.com/cloudfront/) (CF), the respective CF URL. Image thumbnails are also copied to S3 and delivered through S3 or CF.

You'll also find a new icon next to the "Add Media" button when editing a post. This allows you to easily browse and manage files in S3.

**Request features, report bugs, and submit pull requests on [Github](https://github.com/bradt/wp-tantan-s3/)**

*This plugin is a fork of 
[Amazon S3 for WordPress with CloudFront](http://wordpress.org/extend/plugins/tantan-s3-cloudfront/) 
which is a fork of [Amazon S3 for WordPress](http://wordpress.org/extend/plugins/tantan-s3/), also known as tantan-s3. See the [Change Log](https://github.com/bradt/wp-tantan-s3/commits/master) to see what has been done so far.*

== Installation ==

1. Use WordPress' built-in installer
2. Access the Amazon S3 option under Settings and configure your Amazon details

== Screenshots ==

1. The settings screen for the plugin
2. Browse files in a Amazon S3 bucket

== Changelog ==

= 0.5 - 2013-01-29 =
* Forked [Amazon S3 for WordPress with CloudFront](http://wordpress.org/extend/plugins/tantan-s3-cloudfront/)
* Fixed issues causing error messages when WP_DEBUG is on
* [Delete files on S3 when deleting WP attachment](https://github.com/bradt/wp-tantan-s3/commit/e777cd49a4b6999f999bd969241fb24cbbcece60)
* [Added filter to the get_attachment_url function](https://github.com/bradt/wp-tantan-s3/commit/bbe1aed5c2ae900e9ba1b16ba6806c28ab8e2f1c)
* [Added function to get a temporary, secure download URL for private files](https://github.com/bradt/wp-tantan-s3/commit/11f46ec2714d34907009e37ad3b97f4421aefed3)
