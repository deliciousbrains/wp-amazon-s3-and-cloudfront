<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\ErrorResponse;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Operation;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\ErrorResponse\Exception\ErrorResponseException;
use DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface;
/**
 * Converts generic Guzzle response exceptions into errorResponse exceptions
 */
class ErrorResponsePlugin implements \DeliciousBrains\WP_Offload_S3\Aws2\Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array('command.before_send' => array('onCommandBeforeSend', -1));
    }
    /**
     * Adds a listener to requests before they sent from a command
     *
     * @param Event $event Event emitted
     */
    public function onCommandBeforeSend(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event $event)
    {
        $command = $event['command'];
        if ($operation = $command->getOperation()) {
            if ($operation->getErrorResponses()) {
                $request = $command->getRequest();
                $request->getEventDispatcher()->addListener('request.complete', $this->getErrorClosure($request, $command, $operation));
            }
        }
    }
    /**
     * @param RequestInterface $request   Request that received an error
     * @param CommandInterface $command   Command that created the request
     * @param Operation        $operation Operation that defines the request and errors
     *
     * @return \Closure Returns a closure
     * @throws ErrorResponseException
     */
    protected function getErrorClosure(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Operation $operation)
    {
        return function (\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event $event) use($request, $command, $operation) {
            $response = $event['response'];
            foreach ($operation->getErrorResponses() as $error) {
                if (!isset($error['class'])) {
                    continue;
                }
                if (isset($error['code']) && $response->getStatusCode() != $error['code']) {
                    continue;
                }
                if (isset($error['reason']) && $response->getReasonPhrase() != $error['reason']) {
                    continue;
                }
                $className = $error['class'];
                $errorClassInterface = __NAMESPACE__ . '\\ErrorResponseExceptionInterface';
                if (!class_exists($className)) {
                    throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\ErrorResponse\Exception\ErrorResponseException("{$className} does not exist");
                } elseif (!in_array($errorClassInterface, class_implements($className))) {
                    throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\ErrorResponse\Exception\ErrorResponseException("{$className} must implement {$errorClassInterface}");
                }
                throw $className::fromCommand($command, $response);
            }
        };
    }
}
