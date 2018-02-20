<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Request;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter;
/**
 * Visitor used to apply a parameter to a request's query string
 */
class QueryVisitor extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Request\AbstractRequestVisitor
{
    public function visit(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $param, $value)
    {
        $request->getQuery()->set($param->getWireName(), $this->prepareValue($value, $param));
    }
}
