<?php

namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Signature;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Credentials\CredentialsInterface;
use DeliciousBrains\WP_Offload_Media\Aws3\Psr\Http\Message\RequestInterface;
/**
 * Provides anonymous client access (does not sign requests).
 */
class AnonymousSignature implements \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Signature\SignatureInterface
{
    public function signRequest(\DeliciousBrains\WP_Offload_Media\Aws3\Psr\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Credentials\CredentialsInterface $credentials)
    {
        return $request;
    }
    public function presign(\DeliciousBrains\WP_Offload_Media\Aws3\Psr\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Credentials\CredentialsInterface $credentials, $expires, array $options = [])
    {
        return $request;
    }
}
