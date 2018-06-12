<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\StructureShape;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * @internal Implements REST-XML parsing (e.g., S3, CloudFront, etc...)
 */
class RestXmlParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractRestParser
{
    use PayloadParserTrait;
    /** @var XmlParser */
    private $parser;
    /**
     * @param Service   $api    Service description
     * @param XmlParser $parser XML body parser
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service $api, \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\XmlParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\XmlParser();
    }
    protected function payload(\DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response, \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\StructureShape $member, array &$result)
    {
        $xml = $this->parseXml($response->getBody());
        $result += $this->parser->parse($member, $xml);
    }
}
