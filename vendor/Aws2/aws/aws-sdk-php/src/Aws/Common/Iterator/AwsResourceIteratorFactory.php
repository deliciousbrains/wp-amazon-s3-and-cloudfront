<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Iterator;

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorFactoryInterface;
/**
 * Resource iterator factory used to instantiate the default AWS resource iterator with the correct configuration or
 * use a concrete iterator class if one exists
 */
class AwsResourceIteratorFactory implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorFactoryInterface
{
    /**
     * @var array Default configuration values for iterators
     */
    protected static $defaultIteratorConfig = array('input_token' => null, 'output_token' => null, 'limit_key' => null, 'result_key' => null, 'more_results' => null);
    /**
     * @var array Legacy configuration options mapped to their new names
     */
    private static $legacyConfigOptions = array('token_param' => 'input_token', 'token_key' => 'output_token', 'limit_param' => 'limit_key', 'more_key' => 'more_results');
    /**
     * @var array Iterator configuration for each iterable operation
     */
    protected $config;
    /**
     * @var ResourceIteratorFactoryInterface Another factory that will be used first to instantiate the iterator
     */
    protected $primaryIteratorFactory;
    /**
     * @param array                            $config                 An array of configuration values for the factory
     * @param ResourceIteratorFactoryInterface $primaryIteratorFactory Another factory to use for chain of command
     */
    public function __construct(array $config, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorFactoryInterface $primaryIteratorFactory = null)
    {
        $this->primaryIteratorFactory = $primaryIteratorFactory;
        $this->config = array();
        foreach ($config as $name => $operation) {
            $this->config[$name] = $operation + self::$defaultIteratorConfig;
        }
    }
    public function build(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command, array $options = array())
    {
        // Get the configuration data for the command
        $commandName = $command->getName();
        $commandSupported = isset($this->config[$commandName]);
        $options = $this->translateLegacyConfigOptions($options);
        $options += $commandSupported ? $this->config[$commandName] : array();
        // Instantiate the iterator using the primary factory (if one was provided)
        if ($this->primaryIteratorFactory && $this->primaryIteratorFactory->canBuild($command)) {
            $iterator = $this->primaryIteratorFactory->build($command, $options);
        } elseif (!$commandSupported) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException("Iterator was not found for {$commandName}.");
        } else {
            // Instantiate a generic AWS resource iterator
            $iterator = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Iterator\AwsResourceIterator($command, $options);
        }
        return $iterator;
    }
    public function canBuild(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        if ($this->primaryIteratorFactory) {
            return $this->primaryIteratorFactory->canBuild($command);
        } else {
            return isset($this->config[$command->getName()]);
        }
    }
    /**
     * @param array $config The config for a single operation
     *
     * @return array The modified config with legacy options translated
     */
    private function translateLegacyConfigOptions($config)
    {
        foreach (self::$legacyConfigOptions as $legacyOption => $newOption) {
            if (isset($config[$legacyOption])) {
                $config[$newOption] = $config[$legacyOption];
                unset($config[$legacyOption]);
            }
        }
        return $config;
    }
}
