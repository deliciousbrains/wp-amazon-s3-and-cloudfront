<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\StructureShape;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * @internal Implements REST-JSON parsing (e.g., Glacier, Elastic Transcoder)
 */
class RestJsonParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractRestParser
{
    use PayloadParserTrait;
    /** @var JsonParser */
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
    protected function payload(\DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response, \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\StructureShape $member, array &$result)
    {
        $jsonBody = $this->parseJson($response->getBody());
        if ($jsonBody) {
            $result += $this->parser->parse($member, $jsonBody);
        }
    }
}
