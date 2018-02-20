<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event;
/**
 * Event class emitted with the operation.parse_class event
 */
class CreateResponseClassEvent extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event
{
    /**
     * Set the result of the object creation
     *
     * @param mixed $result Result value to set
     */
    public function setResult($result)
    {
        $this['result'] = $result;
        $this->stopPropagation();
    }
    /**
     * Get the created object
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this['result'];
    }
}
