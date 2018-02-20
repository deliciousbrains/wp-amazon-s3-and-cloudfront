<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Mimetypes;
/**
 * POST file upload
 */
class PostFile implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\PostFileInterface
{
    protected $fieldName;
    protected $contentType;
    protected $filename;
    protected $postname;
    /**
     * @param string $fieldName   Name of the field
     * @param string $filename    Local path to the file
     * @param string $postname    Remote post file name
     * @param string $contentType Content-Type of the upload
     */
    public function __construct($fieldName, $filename, $contentType = null, $postname = null)
    {
        $this->fieldName = $fieldName;
        $this->setFilename($filename);
        $this->postname = $postname ? $postname : basename($filename);
        $this->contentType = $contentType ?: $this->guessContentType();
    }
    public function setFieldName($name)
    {
        $this->fieldName = $name;
        return $this;
    }
    public function getFieldName()
    {
        return $this->fieldName;
    }
    public function setFilename($filename)
    {
        // Remove leading @ symbol
        if (strpos($filename, '@') === 0) {
            $filename = substr($filename, 1);
        }
        if (!is_readable($filename)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Exception\InvalidArgumentException("Unable to open {$filename} for reading");
        }
        $this->filename = $filename;
        return $this;
    }
    public function setPostname($postname)
    {
        $this->postname = $postname;
        return $this;
    }
    public function getFilename()
    {
        return $this->filename;
    }
    public function getPostname()
    {
        return $this->postname;
    }
    public function setContentType($type)
    {
        $this->contentType = $type;
        return $this;
    }
    public function getContentType()
    {
        return $this->contentType;
    }
    public function getCurlValue()
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($this->filename, $this->contentType, $this->postname);
        }
        // Use the old style if using an older version of PHP
        $value = "@{$this->filename};filename=" . $this->postname;
        if ($this->contentType) {
            $value .= ';type=' . $this->contentType;
        }
        return $value;
    }
    /**
     * @deprecated
     * @codeCoverageIgnore
     */
    public function getCurlString()
    {
        \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version::warn(__METHOD__ . ' is deprecated. Use getCurlValue()');
        return $this->getCurlValue();
    }
    /**
     * Determine the Content-Type of the file
     */
    protected function guessContentType()
    {
        return \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Mimetypes::getInstance()->fromFilename($this->filename) ?: 'application/octet-stream';
    }
}
