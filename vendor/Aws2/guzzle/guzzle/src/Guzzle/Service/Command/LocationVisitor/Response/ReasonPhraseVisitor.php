<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
/**
 * Location visitor used to add the reason phrase of a response to a key in the result
 */
class ReasonPhraseVisitor extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response\AbstractResponseVisitor
{
    public function visit(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $param, &$value, $context = null)
    {
        $value[$param->getName()] = $response->getReasonPhrase();
    }
}
