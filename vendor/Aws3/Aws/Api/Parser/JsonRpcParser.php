<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Result;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * @internal Implements JSON-RPC parsing (e.g., DynamoDB)
 */
class JsonRpcParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser
{
    use PayloadParserTrait;
    private $parser;
    /**
     * @param Service    $api    Service description
     * @param JsonParser $parser JSON body builder
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service $api, \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\JsonParser();
    }
    public function __invoke(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response)
    {
        $operation = $this->api->getOperation($command->getName());
        $result = null === $operation['output'] ? null : $this->parser->parse($operation->getOutput(), $this->parseJson($response->getBody()));
        return new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Result($result ?: []);
    }
}
