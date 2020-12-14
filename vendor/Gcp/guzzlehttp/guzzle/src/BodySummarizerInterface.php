<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp;

use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\MessageInterface $message) : ?string;
}
