<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\Aws\Handler\GuzzleV5;

use DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Stream\StreamDecoratorTrait;
use DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Stream\StreamInterface as GuzzleStreamInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\StreamInterface as Psr7StreamInterface;
/**
 * Adapts a PSR-7 Stream to a Guzzle 5 Stream.
 *
 * @codeCoverageIgnore
 */
class GuzzleStream implements \DeliciousBrains\WP_Offload_S3\Aws3\GuzzleHttp\Stream\StreamInterface
{
    use StreamDecoratorTrait;
    /** @var Psr7StreamInterface */
    private $stream;
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws3\Psr\Http\Message\StreamInterface $stream)
    {
        $this->stream = $stream;
    }
}
