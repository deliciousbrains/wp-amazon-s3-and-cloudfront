<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Curl\CurlHandle;
/**
 * cURL request exception
 */
class CurlException extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\RequestException
{
    private $curlError;
    private $curlErrorNo;
    private $handle;
    private $curlInfo = array();
    /**
     * Set the cURL error message
     *
     * @param string $error  Curl error
     * @param int    $number Curl error number
     *
     * @return self
     */
    public function setError($error, $number)
    {
        $this->curlError = $error;
        $this->curlErrorNo = $number;
        return $this;
    }
    /**
     * Set the associated curl handle
     *
     * @param CurlHandle $handle Curl handle
     *
     * @return self
     */
    public function setCurlHandle(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Curl\CurlHandle $handle)
    {
        $this->handle = $handle;
        return $this;
    }
    /**
     * Get the associated cURL handle
     *
     * @return CurlHandle|null
     */
    public function getCurlHandle()
    {
        return $this->handle;
    }
    /**
     * Get the associated cURL error message
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->curlError;
    }
    /**
     * Get the associated cURL error number
     *
     * @return int|null
     */
    public function getErrorNo()
    {
        return $this->curlErrorNo;
    }
    /**
     * Returns curl information about the transfer
     *
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->curlInfo;
    }
    /**
     * Set curl transfer information
     *
     * @param array $info Array of curl transfer information
     *
     * @return self
     * @link http://php.net/manual/en/function.curl-getinfo.php
     */
    public function setCurlInfo(array $info)
    {
        $this->curlInfo = $info;
        return $this;
    }
}
