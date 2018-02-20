<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Parser\UriTemplate;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\RuntimeException;
/**
 * Expands URI templates using the uri_template pecl extension (pecl install uri_template-beta)
 *
 * @link http://pecl.php.net/package/uri_template
 * @link https://github.com/ioseb/uri-template
 */
class PeclUriTemplate implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Parser\UriTemplate\UriTemplateInterface
{
    public function __construct()
    {
        if (!extension_loaded('uri_template')) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\RuntimeException('uri_template PECL extension must be installed to use PeclUriTemplate');
        }
    }
    public function expand($template, array $variables)
    {
        return uri_template($template, $variables);
    }
}
