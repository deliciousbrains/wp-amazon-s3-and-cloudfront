<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response\ResponseVisitorInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\OperationInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Operation;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Exception\ResponseClassException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\Model;
/**
 * Response parser that attempts to marshal responses into an associative array based on models in a service description
 */
class OperationResponseParser extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\DefaultResponseParser
{
    /** @var VisitorFlyweight $factory Visitor factory */
    protected $factory;
    /** @var self */
    protected static $instance;
    /** @var bool */
    private $schemaInModels;
    /**
     * @return self
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight::getInstance());
        }
        return static::$instance;
    }
    /**
     * @param VisitorFlyweight $factory        Factory to use when creating visitors
     * @param bool             $schemaInModels Set to true to inject schemas into models
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight $factory, $schemaInModels = false)
    {
        $this->factory = $factory;
        $this->schemaInModels = $schemaInModels;
    }
    /**
     * Add a location visitor to the command
     *
     * @param string                   $location Location to associate with the visitor
     * @param ResponseVisitorInterface $visitor  Visitor to attach
     *
     * @return self
     */
    public function addVisitor($location, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\LocationVisitor\Response\ResponseVisitorInterface $visitor)
    {
        $this->factory->addResponseVisitor($location, $visitor);
        return $this;
    }
    protected function handleParsing(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response, $contentType)
    {
        $operation = $command->getOperation();
        $type = $operation->getResponseType();
        $model = null;
        if ($type == \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\OperationInterface::TYPE_MODEL) {
            $model = $operation->getServiceDescription()->getModel($operation->getResponseClass());
        } elseif ($type == \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\OperationInterface::TYPE_CLASS) {
            return $this->parseClass($command);
        }
        if (!$model) {
            // Return basic processing if the responseType is not model or the model cannot be found
            return parent::handleParsing($command, $response, $contentType);
        } elseif ($command[\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\AbstractCommand::RESPONSE_PROCESSING] != \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\AbstractCommand::TYPE_MODEL) {
            // Returns a model with no visiting if the command response processing is not model
            return new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\Model(parent::handleParsing($command, $response, $contentType));
        } else {
            // Only inject the schema into the model if "schemaInModel" is true
            return new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\Model($this->visitResult($model, $command, $response), $this->schemaInModels ? $model : null);
        }
    }
    /**
     * Parse a class object
     *
     * @param CommandInterface $command Command to parse into an object
     *
     * @return mixed
     * @throws ResponseClassException
     */
    protected function parseClass(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        // Emit the operation.parse_class event. If a listener injects a 'result' property, then that will be the result
        $event = new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CreateResponseClassEvent(array('command' => $command));
        $command->getClient()->getEventDispatcher()->dispatch('command.parse_response', $event);
        if ($result = $event->getResult()) {
            return $result;
        }
        $className = $command->getOperation()->getResponseClass();
        if (!method_exists($className, 'fromCommand')) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Exception\ResponseClassException("{$className} must exist and implement a static fromCommand() method");
        }
        return $className::fromCommand($command);
    }
    /**
     * Perform transformations on the result array
     *
     * @param Parameter        $model    Model that defines the structure
     * @param CommandInterface $command  Command that performed the operation
     * @param Response         $response Response received
     *
     * @return array Returns the array of result data
     */
    protected function visitResult(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $model, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        $foundVisitors = $result = $knownProps = array();
        $props = $model->getProperties();
        foreach ($props as $schema) {
            if ($location = $schema->getLocation()) {
                // Trigger the before method on the first found visitor of this type
                if (!isset($foundVisitors[$location])) {
                    $foundVisitors[$location] = $this->factory->getResponseVisitor($location);
                    $foundVisitors[$location]->before($command, $result);
                }
            }
        }
        // Visit additional properties when it is an actual schema
        if (($additional = $model->getAdditionalProperties()) instanceof Parameter) {
            $this->visitAdditionalProperties($model, $command, $response, $additional, $result, $foundVisitors);
        }
        // Apply the parameter value with the location visitor
        foreach ($props as $schema) {
            $knownProps[$schema->getName()] = 1;
            if ($location = $schema->getLocation()) {
                $foundVisitors[$location]->visit($command, $response, $schema, $result);
            }
        }
        // Remove any unknown and potentially unsafe top-level properties
        if ($additional === false) {
            $result = array_intersect_key($result, $knownProps);
        }
        // Call the after() method of each found visitor
        foreach ($foundVisitors as $visitor) {
            $visitor->after($command);
        }
        return $result;
    }
    protected function visitAdditionalProperties(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $model, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\Parameter $additional, &$result, array &$foundVisitors)
    {
        // Only visit when a location is specified
        if ($location = $additional->getLocation()) {
            if (!isset($foundVisitors[$location])) {
                $foundVisitors[$location] = $this->factory->getResponseVisitor($location);
                $foundVisitors[$location]->before($command, $result);
            }
            // Only traverse if an array was parsed from the before() visitors
            if (is_array($result)) {
                // Find each additional property
                foreach (array_keys($result) as $key) {
                    // Check if the model actually knows this property. If so, then it is not additional
                    if (!$model->getProperty($key)) {
                        // Set the name to the key so that we can parse it with each visitor
                        $additional->setName($key);
                        $foundVisitors[$location]->visit($command, $response, $additional, $result);
                    }
                }
                // Reset the additionalProperties name to null
                $additional->setName(null);
            }
        }
    }
}
