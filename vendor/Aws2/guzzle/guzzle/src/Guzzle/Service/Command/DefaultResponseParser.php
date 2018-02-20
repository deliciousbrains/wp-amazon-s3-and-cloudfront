<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
/**
 * Default HTTP response parser used to marshal JSON responses into arrays and XML responses into SimpleXMLElement
 */
class DefaultResponseParser implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\ResponseParserInterface
{
    /** @var self */
    protected static $instance;
    /**
     * @return self
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function parse(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        $response = $command->getRequest()->getResponse();
        // Account for hard coded content-type values specified in service descriptions
        if ($contentType = $command['command.expects']) {
            $response->setHeader('Content-Type', $contentType);
        } else {
            $contentType = (string) $response->getHeader('Content-Type');
        }
        return $this->handleParsing($command, $response, $contentType);
    }
    protected function handleParsing(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response, $contentType)
    {
        $result = $response;
        if ($result->getBody()) {
            if (stripos($contentType, 'json') !== false) {
                $result = $result->json();
            } elseif (stripos($contentType, 'xml') !== false) {
                $result = $result->xml();
            }
        }
        return $result;
    }
}
