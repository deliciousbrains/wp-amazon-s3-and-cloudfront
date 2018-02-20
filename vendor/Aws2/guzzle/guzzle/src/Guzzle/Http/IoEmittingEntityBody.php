<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\HasDispatcherInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcher;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcherInterface;
/**
 * EntityBody decorator that emits events for read and write methods
 */
class IoEmittingEntityBody extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\AbstractEntityBodyDecorator implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\HasDispatcherInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    public static function getAllEvents()
    {
        return array('body.read', 'body.write');
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
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
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function addSubscriber(\DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber)
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
        return $this;
    }
    public function read($length)
    {
        $event = array('body' => $this, 'length' => $length, 'read' => $this->body->read($length));
        $this->dispatch('body.read', $event);
        return $event['read'];
    }
    public function write($string)
    {
        $event = array('body' => $this, 'write' => $string, 'result' => $this->body->write($string));
        $this->dispatch('body.write', $event);
        return $event['result'];
    }
}
