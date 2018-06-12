<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\Exception\ParserException;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Exception\AwsException;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * Converts malformed responses to a retryable error type.
 *
 * @internal
 */
class RetryableMalformedResponseParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser
{
    /** @var callable */
    private $parser;
    /** @var string */
    private $exceptionClass;
    public function __construct(callable $parser, $exceptionClass = \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Exception\AwsException::class)
    {
        $this->parser = $parser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response)
    {
        $fn = $this->parser;
        try {
            return $fn($command, $response);
        } catch (ParserException $e) {
            throw new $this->exceptionClass("Error parsing response for {$command->getName()}:" . " AWS parsing error: {$e->getMessage()}", $command, ['connection_error' => true, 'exception' => $e], $e);
        }
    }
}
