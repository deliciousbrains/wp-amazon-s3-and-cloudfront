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

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Aws;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions as Options;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\TransferException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\RulesEndpointProvider;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\EndpointSignatureInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureListener;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterClassFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\CompositeWaiterFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterFactoryInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterConfigFactory;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\CurlException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator\DuplicateAggregator;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Client;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\ServiceDescriptionInterface;
/**
 * Abstract AWS client
 */
abstract class AbstractClient extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Client implements \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\AwsClientInterface
{
    /** @var CredentialsInterface AWS credentials */
    protected $credentials;
    /** @var SignatureInterface Signature implementation of the service */
    protected $signature;
    /** @var WaiterFactoryInterface Factory used to create waiter classes */
    protected $waiterFactory;
    /** @var DuplicateAggregator Cached query aggregator*/
    protected $aggregator;
    /**
     * {@inheritdoc}
     */
    public static function getAllEvents()
    {
        return array_merge(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Client::getAllEvents(), array('client.region_changed', 'client.credentials_changed'));
    }
    /**
     * @param CredentialsInterface $credentials AWS credentials
     * @param SignatureInterface   $signature   Signature implementation
     * @param Collection           $config      Configuration options
     *
     * @throws InvalidArgumentException if an endpoint provider isn't provided
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface $credentials, \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureInterface $signature, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Collection $config)
    {
        // Bootstrap with Guzzle
        parent::__construct($config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BASE_URL), $config);
        $this->credentials = $credentials;
        $this->signature = $signature;
        $this->aggregator = new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator\DuplicateAggregator();
        // Make sure the user agent is prefixed by the SDK version
        $this->setUserAgent('aws-sdk-php2/' . \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Aws::VERSION, true);
        // Add the event listener so that requests are signed before they are sent
        $dispatcher = $this->getEventDispatcher();
        $dispatcher->addSubscriber(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureListener($credentials, $signature));
        if ($backoff = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BACKOFF)) {
            $dispatcher->addSubscriber($backoff, -255);
        }
    }
    public function __call($method, $args)
    {
        if (substr($method, 0, 3) === 'get' && substr($method, -8) === 'Iterator') {
            // Allow magic method calls for iterators (e.g. $client->get<CommandName>Iterator($params))
            $commandOptions = isset($args[0]) ? $args[0] : null;
            $iteratorOptions = isset($args[1]) ? $args[1] : array();
            return $this->getIterator(substr($method, 3, -8), $commandOptions, $iteratorOptions);
        } elseif (substr($method, 0, 9) == 'waitUntil') {
            // Allow magic method calls for waiters (e.g. $client->waitUntil<WaiterName>($params))
            return $this->waitUntil(substr($method, 9), isset($args[0]) ? $args[0] : array());
        } else {
            return parent::__call(ucfirst($method), $args);
        }
    }
    /**
     * Get an endpoint for a specific region from a service description
     * @deprecated This function will no longer be updated to work with new regions.
     */
    public static function getEndpoint(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Description\ServiceDescriptionInterface $description, $region, $scheme)
    {
        try {
            $service = $description->getData('endpointPrefix');
            $provider = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\RulesEndpointProvider::fromDefaults();
            $result = $provider(array('service' => $service, 'region' => $region, 'scheme' => $scheme));
            return $result['endpoint'];
        } catch (\InvalidArgumentException $e) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException($e->getMessage(), 0, $e);
        }
    }
    public function getCredentials()
    {
        return $this->credentials;
    }
    public function setCredentials(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface $credentials)
    {
        $formerCredentials = $this->credentials;
        $this->credentials = $credentials;
        // Dispatch an event that the credentials have been changed
        $this->dispatch('client.credentials_changed', array('credentials' => $credentials, 'former_credentials' => $formerCredentials));
        return $this;
    }
    public function getSignature()
    {
        return $this->signature;
    }
    public function getRegions()
    {
        return $this->serviceDescription->getData('regions');
    }
    public function getRegion()
    {
        return $this->getConfig(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION);
    }
    public function setRegion($region)
    {
        $config = $this->getConfig();
        $formerRegion = $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION);
        $global = $this->serviceDescription->getData('globalEndpoint');
        $provider = $config->get('endpoint_provider');
        if (!$provider) {
            throw new \RuntimeException('No endpoint provider configured');
        }
        // Only change the region if the service does not have a global endpoint
        if (!$global || $this->serviceDescription->getData('namespace') === 'S3') {
            $endpoint = call_user_func($provider, array('scheme' => $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SCHEME), 'region' => $region, 'service' => $config->get(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::SERVICE)));
            $this->setBaseUrl($endpoint['endpoint']);
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::BASE_URL, $endpoint['endpoint']);
            $config->set(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\ClientOptions::REGION, $region);
            // Update the signature if necessary
            $signature = $this->getSignature();
            if ($signature instanceof EndpointSignatureInterface) {
                /** @var EndpointSignatureInterface $signature */
                $signature->setRegionName($region);
            }
            // Dispatch an event that the region has been changed
            $this->dispatch('client.region_changed', array('region' => $region, 'former_region' => $formerRegion));
        }
        return $this;
    }
    public function waitUntil($waiter, array $input = array())
    {
        $this->getWaiter($waiter, $input)->wait();
        return $this;
    }
    public function getWaiter($waiter, array $input = array())
    {
        return $this->getWaiterFactory()->build($waiter)->setClient($this)->setConfig($input);
    }
    public function setWaiterFactory(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterFactoryInterface $waiterFactory)
    {
        $this->waiterFactory = $waiterFactory;
        return $this;
    }
    public function getWaiterFactory()
    {
        if (!$this->waiterFactory) {
            $clientClass = get_class($this);
            // Use a composite factory that checks for classes first, then config waiters
            $this->waiterFactory = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\CompositeWaiterFactory(array(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterClassFactory(substr($clientClass, 0, strrpos($clientClass, '\\')) . '\\Waiter')));
            if ($this->getDescription()) {
                $waiterConfig = $this->getDescription()->getData('waiters') ?: array();
                $this->waiterFactory->addFactory(new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Waiter\WaiterConfigFactory($waiterConfig));
            }
        }
        return $this->waiterFactory;
    }
    public function getApiVersion()
    {
        return $this->serviceDescription->getApiVersion();
    }
    /**
     * {@inheritdoc}
     * @throws \Aws\Common\Exception\TransferException
     */
    public function send($requests)
    {
        try {
            return parent::send($requests);
        } catch (CurlException $e) {
            $wrapped = new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\TransferException($e->getMessage(), null, $e);
            $wrapped->setCurlHandle($e->getCurlHandle())->setCurlInfo($e->getCurlInfo())->setError($e->getError(), $e->getErrorNo())->setRequest($e->getRequest());
            throw $wrapped;
        }
    }
    /**
     * Ensures that the duplicate query string aggregator is used so that
     * query string values are sent over the wire as foo=bar&foo=baz.
     * {@inheritdoc}
     */
    public function createRequest($method = 'GET', $uri = null, $headers = null, $body = null, array $options = array())
    {
        $request = parent::createRequest($method, $uri, $headers, $body, $options);
        $request->getQuery()->setAggregator($this->aggregator);
        return $request;
    }
}
