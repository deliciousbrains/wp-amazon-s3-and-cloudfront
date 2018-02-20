<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common;

use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcher;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Class that holds an event dispatcher
 */
class AbstractHasDispatcher implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\HasDispatcherInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    public static function getAllEvents()
    {
        return array();
    }
    public function setEventDispatcher(\DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
    public function getEventDispatcher()
    {
        if (!$this->eventDispatcher) {
            $this->eventDispatcher = new \DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcher();
        }
        return $this->eventDispatcher;
    }
    public function dispatch($eventName, array $context = array())
    {
        return $this->getEventDispatcher()->dispatch($eventName, new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event($context));
    }
    public function addSubscriber(\DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
        return $this;
    }
}
