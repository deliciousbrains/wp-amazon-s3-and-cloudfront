<?php

namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\UseArnRegion;

use Aws;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\UseArnRegion\Exception\ConfigurationException;
class Configuration implements \DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\UseArnRegion\ConfigurationInterface
{
    private $useArnRegion;
    public function __construct($useArnRegion)
    {
        $this->useArnRegion = \DeliciousBrains\WP_Offload_Media\Aws3\Aws\boolean_value($useArnRegion);
        if (is_null($this->useArnRegion)) {
            throw new \DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\UseArnRegion\Exception\ConfigurationException("'use_arn_region' config option" . " must be a boolean value.");
        }
    }
    /**
     * {@inheritdoc}
     */
    public function isUseArnRegion()
    {
        return $this->useArnRegion;
    }
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return ['use_arn_region' => $this->isUseArnRegion()];
    }
}
