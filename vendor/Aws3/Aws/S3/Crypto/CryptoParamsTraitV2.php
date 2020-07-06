<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\Crypto;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Crypto\MaterialsProviderInterface;

trait CryptoParamsTraitV2
{
    use CryptoParamsTrait;

    protected function getMaterialsProvider(array $args)
    {
        if ($args['@MaterialsProvider'] instanceof MaterialsProviderInterface) {
            return $args['@MaterialsProvider'];
        }

        throw new \InvalidArgumentException('An instance of MaterialsProviderInterface'
            . ' must be passed in the "MaterialsProvider" field.');
    }
}
