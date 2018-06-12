<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Result;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * @internal Parses query (XML) responses (e.g., EC2, SQS, and many others)
 */
class QueryParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser
{
    use PayloadParserTrait;
    /** @var XmlParser */
    private $xmlParser;
    /** @var bool */
    private $honorResultWrapper;
    /**
     * @param Service   $api                Service description
     * @param XmlParser $xmlParser          Optional XML parser
     * @param bool      $honorResultWrapper Set to false to disable the peeling
     *                                      back of result wrappers from the
     *                                      output structure.
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Service $api, \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\XmlParser $xmlParser = null, $honorResultWrapper = true)
    {
        parent::__construct($api);
        $this->xmlParser = $xmlParser ?: new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\XmlParser();
        $this->honorResultWrapper = $honorResultWrapper;
    }
    public function __invoke(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response)
    {
        $output = $this->api->getOperation($command->getName())->getOutput();
        $xml = $this->parseXml($response->getBody());
        if ($this->honorResultWrapper && $output['resultWrapper']) {
            $xml = $xml->{$output['resultWrapper']};
        }
        return new \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Result($this->xmlParser->parse($output, $xml));
    }
}
