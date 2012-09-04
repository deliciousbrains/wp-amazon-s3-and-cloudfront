=== Plugin Name ===
Contributors: joetan
Tags: uploads, amazon, s3, mirror, admin, media
Requires at least: 2.3
Tested up to: 2.7
Stable tag: 0.3.4

Allows you to mirror your WordPress media uploads over to Amazon S3 for storage and delivery.

== Description ==

This WordPress plugin allows you to use Amazon's Simple Storage Service to host your media for your WordPress powered blog.

Amazon S3 is a cheap and cost effective way to scale your site to easily handle large spikes in traffic (such as from Digg) without having to go through the expense of setting up the infrastructure for a content delivery network.

Once setup, this plugin transparently integrates with your WordPress blog. File uploads are automatically saved into your Amazon S3 bucket without any extra steps. Once saved, these files will be delivered by Amazon S3, instead of your web host. Any image thumbnails that get created are saved to Amazon S3 too. You'll also find an "Amazon S3" tab next to your regular "Upload" tab, which allows you to easily browse and manage files that were not upload via WordPress.

== Installation ==

1. Upload `tantan-s3` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin in the 'Options' (or 'Settings') screen by following the onscreen prompts.

## Documentation
If you need more help installing and configuring the plugin, [see here for more information](http://code.google.com/p/wordpress-s3/wiki/Documentation). 

== Screenshots ==

1. The settings screen for the plugin
2. Browse files in a Amazon S3 bucket
