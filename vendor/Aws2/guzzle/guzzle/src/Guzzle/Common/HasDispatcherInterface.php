<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common;

use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Holds an event dispatcher
 */
interface HasDispatcherInterface
{
    /**
     * Get a list of all of the events emitted from the class
     *
     * @return array
     */
    public static function getAllEvents();
    /**
     * Set the EventDispatcher of the request
     *
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return self
     */
    public function setEventDispatcher(\DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher);
    /**
     * Get the EventDispatcher of the request
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
    /**
     * Helper to dispatch Guzzle events and set the event name on the event
     *
     * @param string $eventName Name of the event to dispatch
     * @param array  $context   Context of the event
     *
     * @return Event Returns the created event object
     */
    public function dispatch($eventName, array $context = array());
    /**
     * Add an event subscriber to the dispatcher
     *
     * @param EventSubscriberInterface $subscriber Event subscriber
     *
     * @return self
     */
    public function addSubscriber(\DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber);
}
