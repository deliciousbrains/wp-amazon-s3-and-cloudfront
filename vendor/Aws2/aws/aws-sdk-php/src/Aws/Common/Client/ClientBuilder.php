<?php

/**
 * Copyright 2010-2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client;

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\Credentials;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\NullCredentials;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions as Options;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\ExceptionListener;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\NamespaceExceptionFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\DefaultXmlExceptionParser;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\ExceptionParserInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Iterator\AwsResourceIteratorFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\RulesEndpointProvider;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\EndpointSignatureInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV2;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV3Https;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV4;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffPlugin;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\CurlBackoffStrategy;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\ExponentialBackoffStrategy;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\HttpBackoffStrategy;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\TruncatedBackoffStrategy;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\ServiceDescription;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorClassFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\LogAdapterInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\ClosureLogAdapter;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffLogger;
/**
 * Builder for creating AWS service clients
 */
class ClientBuilder
{
    /**
     * @var array Default client config
     */
    protected static $commonConfigDefaults = array('scheme' => 'https');
    /**
     * @var array Default client requirements
     */
    protected static $commonConfigRequirements = array(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE_DESCRIPTION);
    /**
     * @var string The namespace of the client
     */
    protected $clientNamespace;
    /**
     * @var array The config options
     */
    protected $config = array();
    /**
     * @var array The config defaults
     */
    protected $configDefaults = array();
    /**
     * @var array The config requirements
     */
    protected $configRequirements = array();
    /**
     * @var ExceptionParserInterface The Parser interface for the client
     */
    protected $exceptionParser;
    /**
     * @var array Array of configuration data for iterators available for the client
     */
    protected $iteratorsConfig = array();
    /** @var string */
    private $clientClass;
    /** @var string */
    private $serviceName;
    /**
     * Factory method for creating the client builder
     *
     * @param string $namespace The namespace of the client
     *
     * @return ClientBuilder
     */
    public static function factory($namespace = null)
    {
        return new static($namespace);
    }
    /**
     * Constructs a client builder
     *
     * @param string $namespace The namespace of the client
     */
    public function __construct($namespace = null)
    {
        $this->clientNamespace = $namespace;
        // Determine service and class name
        $this->clientClass = 'DeliciousBrains\\WP_Offload_S3\\Aws2\\Aws\\Common\\Client\\DefaultClient';
        if ($this->clientNamespace) {
            $this->serviceName = substr($this->clientNamespace, strrpos($this->clientNamespace, '\\') + 1);
            $this->clientClass = $this->clientNamespace . '\\' . $this->serviceName . 'Client';
        }
    }
    /**
     * Sets the config options
     *
     * @param array|Collection $config The config options
     *
     * @return ClientBuilder
     */
    public function setConfig($config)
    {
        $this->config = $this->processArray($config);
        return $this;
    }
    /**
     * Sets the config options' defaults
     *
     * @param array|Collection $defaults The default values
     *
     * @return ClientBuilder
     */
    public function setConfigDefaults($defaults)
    {
        $this->configDefaults = $this->processArray($defaults);
        return $this;
    }
    /**
     * Sets the required config options
     *
     * @param array|Collection $required The required config options
     *
     * @return ClientBuilder
     */
    public function setConfigRequirements($required)
    {
        $this->configRequirements = $this->processArray($required);
        return $this;
    }
    /**
     * Sets the exception parser. If one is not provided the builder will use
     * the default XML exception parser.
     *
     * @param ExceptionParserInterface $parser The exception parser
     *
     * @return ClientBuilder
     */
    public function setExceptionParser(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\ExceptionParserInterface $parser)
    {
        $this->exceptionParser = $parser;
        return $this;
    }
    /**
     * Set the configuration for the client's iterators
     *
     * @param array $config Configuration data for client's iterators
     *
     * @return ClientBuilder
     */
    public function setIteratorsConfig(array $config)
    {
        $this->iteratorsConfig = $config;
        return $this;
    }
    /**
     * Performs the building logic using all of the parameters that have been
     * set and falling back to default values. Returns an instantiate service
     * client with credentials prepared and plugins attached.
     *
     * @return AwsClientInterface
     * @throws InvalidArgumentException
     */
    public function build()
    {
        // Resolve configuration
        $config = \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection::fromConfig($this->config, array_merge(self::$commonConfigDefaults, $this->configDefaults), self::$commonConfigRequirements + $this->configRequirements);
        if ($config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::VERSION] === 'latest') {
            $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::VERSION] = constant("{$this->clientClass}::LATEST_API_VERSION");
        }
        if (!isset($config['endpoint_provider'])) {
            $config['endpoint_provider'] = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\RulesEndpointProvider::fromDefaults();
        }
        // Resolve the endpoint, signature, and credentials
        $description = $this->updateConfigFromDescription($config);
        $signature = $this->getSignature($description, $config);
        $credentials = $this->getCredentials($config);
        $this->extractHttpConfig($config);
        // Resolve exception parser
        if (!$this->exceptionParser) {
            $this->exceptionParser = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\Parser\DefaultXmlExceptionParser();
        }
        // Resolve backoff strategy
        $backoff = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF);
        if ($backoff === null) {
            $retries = isset($config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF_RETRIES]) ? $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF_RETRIES] : 3;
            $backoff = $this->createDefaultBackoff($retries);
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF, $backoff);
        }
        if ($backoff) {
            $this->addBackoffLogger($backoff, $config);
        }
        /** @var AwsClientInterface $client */
        $client = new $this->clientClass($credentials, $signature, $config);
        $client->setDescription($description);
        // Add exception marshaling so that more descriptive exception are thrown
        if ($this->clientNamespace) {
            $exceptionFactory = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\NamespaceExceptionFactory($this->exceptionParser, "{$this->clientNamespace}\\Exception", "{$this->clientNamespace}\\Exception\\{$this->serviceName}Exception");
            $client->addSubscriber(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\ExceptionListener($exceptionFactory));
        }
        // Add the UserAgentPlugin to append to the User-Agent header of requests
        $client->addSubscriber(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\UserAgentListener());
        // Filters used for the cache plugin
        $client->getConfig()->set('params.cache.key_filter', 'header=date,x-amz-date,x-amz-security-token,x-amzn-authorization');
        // Set the iterator resource factory based on the provided iterators config
        $client->setResourceIteratorFactory(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Iterator\AwsResourceIteratorFactory($this->iteratorsConfig, new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorClassFactory($this->clientNamespace . '\\Iterator')));
        // Disable parameter validation if needed
        if ($config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::VALIDATION) === false) {
            $params = $config->get('command.params') ?: array();
            $params['command.disable_validation'] = true;
            $config->set('command.params', $params);
        }
        return $client;
    }
    /**
     * Add backoff logging to the backoff plugin if needed
     *
     * @param BackoffPlugin $plugin Backoff plugin
     * @param Collection    $config Configuration settings
     *
     * @throws InvalidArgumentException
     */
    protected function addBackoffLogger(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffPlugin $plugin, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        // The log option can be set to `debug` or an instance of a LogAdapterInterface
        if ($logger = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF_LOGGER)) {
            $format = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF_LOGGER_TEMPLATE);
            if ($logger === 'debug') {
                $logger = new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\ClosureLogAdapter(function ($message) {
                    trigger_error($message . "\n");
                });
            } elseif (!$logger instanceof LogAdapterInterface) {
                throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF_LOGGER . ' must be set to `debug` or an instance of ' . 'DeliciousBrains\\WP_Offload_S3\\Aws2\\Guzzle\\Common\\Log\\LogAdapterInterface');
            }
            // Create the plugin responsible for logging exponential backoff retries
            $logPlugin = new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffLogger($logger);
            // You can specify a custom format or use the default
            if ($format) {
                $logPlugin->setTemplate($format);
            }
            $plugin->addSubscriber($logPlugin);
        }
    }
    /**
     * Ensures that an array (e.g. for config data) is actually in array form
     *
     * @param array|Collection $array The array data
     *
     * @return array
     * @throws InvalidArgumentException if the arg is not an array or Collection
     */
    protected function processArray($array)
    {
        if ($array instanceof Collection) {
            $array = $array->getAll();
        }
        if (!is_array($array)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException('The config must be provided as an array or Collection.');
        }
        return $array;
    }
    /**
     * Update a configuration object from a service description
     *
     * @param Collection $config Config to update
     *
     * @return ServiceDescription
     * @throws InvalidArgumentException
     */
    protected function updateConfigFromDescription(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        $description = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE_DESCRIPTION);
        if (!$description instanceof ServiceDescription) {
            // Inject the version into the sprintf template if it is a string
            if (is_string($description)) {
                $description = sprintf($description, $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::VERSION));
            }
            $description = \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\ServiceDescription::factory($description);
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE_DESCRIPTION, $description);
        }
        if (!$config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE)) {
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE, $description->getData('endpointPrefix'));
        }
        if ($iterators = $description->getData('iterators')) {
            $this->setIteratorsConfig($iterators);
        }
        $this->handleRegion($config);
        $this->handleEndpoint($config);
        return $description;
    }
    /**
     * Return an appropriate signature object for a a client based on the
     * "signature" configuration setting, or the default signature specified in
     * a service description. The signature can be set to a valid signature
     * version identifier string or an instance of Aws\Common\Signature\SignatureInterface.
     *
     * @param ServiceDescription $description Description that holds a signature option
     * @param Collection         $config      Configuration options
     *
     * @return SignatureInterface
     * @throws InvalidArgumentException
     */
    protected function getSignature(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\ServiceDescription $description, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        // If a custom signature has not been provided, then use the default
        // signature setting specified in the service description.
        $signature = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE) ?: $description->getData('signatureVersion');
        if (is_string($signature)) {
            if ($signature == 'v4') {
                $signature = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV4();
            } elseif ($signature == 'v2') {
                $signature = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV2();
            } elseif ($signature == 'v3https') {
                $signature = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV3Https();
            } else {
                throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException("Invalid signature type: {$signature}");
            }
        } elseif (!$signature instanceof SignatureInterface) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException('The provided signature is not ' . 'a signature version string or an instance of ' . 'DeliciousBrains\\WP_Offload_S3\\Aws2\\Aws\\Common\\Signature\\SignatureInterface');
        }
        // Allow a custom service name or region value to be provided
        if ($signature instanceof EndpointSignatureInterface) {
            // Determine the service name to use when signing
            $signature->setServiceName($config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE_SERVICE) ?: $description->getData('signingName') ?: $description->getData('endpointPrefix'));
            // Determine the region to use when signing requests
            $signature->setRegionName($config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE_REGION) ?: $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION));
        }
        return $signature;
    }
    protected function getCredentials(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        $credentials = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::CREDENTIALS);
        if (is_array($credentials)) {
            $credentials = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\Credentials::factory($credentials);
        } elseif ($credentials === false) {
            $credentials = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\NullCredentials();
        } elseif (!$credentials instanceof CredentialsInterface) {
            $credentials = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\Credentials::factory($config);
        }
        return $credentials;
    }
    private function handleRegion(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        // Make sure a valid region is set
        $region = $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION];
        $description = $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE_DESCRIPTION];
        $global = $description->getData('globalEndpoint');
        if (!$global && !$region) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException('A region is required when using ' . $description->getData('serviceFullName'));
        } elseif ($global && !$region) {
            $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION] = 'us-east-1';
        }
    }
    private function handleEndpoint(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        // Alias "endpoint" with "base_url" for forwards compatibility.
        if ($config['endpoint']) {
            $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BASE_URL] = $config['endpoint'];
            return;
        }
        if ($config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BASE_URL]) {
            return;
        }
        $endpoint = call_user_func($config['endpoint_provider'], array('scheme' => $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SCHEME], 'region' => $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION], 'service' => $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE]));
        $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BASE_URL] = $endpoint['endpoint'];
        // Set a signature if one was not explicitly provided.
        if (!$config->hasKey(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE) && isset($endpoint['signatureVersion'])) {
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE, $endpoint['signatureVersion']);
        }
        // The the signing region if endpoint rule specifies one.
        if (isset($endpoint['credentialScope'])) {
            $scope = $endpoint['credentialScope'];
            if (isset($scope['region'])) {
                $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SIGNATURE_REGION, $scope['region']);
            }
        }
    }
    private function createDefaultBackoff($retries = 3)
    {
        return new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffPlugin(
            // Retry failed requests up to 3 times if it is determined that the request can be retried
            new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\TruncatedBackoffStrategy(
                $retries,
                // Retry failed requests with 400-level responses due to throttling
                new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\ThrottlingErrorChecker(
                    $this->exceptionParser,
                    // Retry failed requests due to transient network or cURL problems
                    new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\CurlBackoffStrategy(
                        null,
                        // Retry failed requests with 500-level responses
                        new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\HttpBackoffStrategy(
                            array(500, 503, 509),
                            // Retry requests that failed due to expired credentials
                            new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\ExpiredCredentialsChecker($this->exceptionParser, new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\ExponentialBackoffStrategy())
                        )
                    )
                )
            )
        );
    }
    private function extractHttpConfig(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        $http = $config['http'];
        if (!is_array($http)) {
            return;
        }
        if (isset($http['verify'])) {
            $config[\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SSL_CERT] = $http['verify'];
        }
    }
}
