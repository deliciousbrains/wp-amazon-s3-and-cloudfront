<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Exception\AwsException;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface;
/**
 * Converts errors returned with a status code of 200 to a retryable error type.
 *
 * @internal
 */
class AmbiguousSuccessParser extends \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Api\Parser\AbstractParser
{
    private static $ambiguousSuccesses = ['UploadPartCopy' => true, 'CopyObject' => true, 'CompleteMultipartUpload' => true];
    /** @var callable */
    private $parser;
    /** @var callable */
    private $errorParser;
    /** @var string */
    private $exceptionClass;
    public function __construct(callable $parser, callable $errorParser, $exceptionClass = \DeliciousBrains\WP_Offload_S3\Aws3\Aws\Exception\AwsException::class)
    {
        $this->parser = $parser;
        $this->errorParser = $errorParser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(\DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\ResponseInterface $response)
    {
        if (200 === $response->getStatusCode() && isset(self::$ambiguousSuccesses[$command->getName()])) {
            $errorParser = $this->errorParser;
            $parsed = $errorParser($response);
            if (isset($parsed['code']) && isset($parsed['message'])) {
                throw new $this->exceptionClass($parsed['message'], $command, ['connection_error' => true]);
            }
        }
        $fn = $this->parser;
        return $fn($command, $response);
    }
}
