<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\CurlAuth;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Adds specified curl auth to all requests sent from a client. Defaults to CURLAUTH_BASIC if none supplied.
 * @deprecated Use $client->getConfig()->setPath('request.options/auth', array('user', 'pass', 'Basic|Digest');
 */
class CurlAuthPlugin implements \DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $username;
    private $password;
    private $scheme;
    /**
     * @param string $username HTTP basic auth username
     * @param string $password Password
     * @param int    $scheme   Curl auth scheme
     */
    public function __construct($username, $password, $scheme = CURLAUTH_BASIC)
    {
        \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version::warn(__CLASS__ . " is deprecated. Use \$client->getConfig()->setPath('request.options/auth', array('user', 'pass', 'Basic|Digest');");
        $this->username = $username;
        $this->password = $password;
        $this->scheme = $scheme;
    }
    public static function getSubscribedEvents()
    {
        return array('client.create_request' => array('onRequestCreate', 255));
    }
    /**
     * Add basic auth
     *
     * @param Event $event
     */
    public function onRequestCreate(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event $event)
    {
        $event['request']->setAuth($this->username, $this->password, $this->scheme);
    }
}
