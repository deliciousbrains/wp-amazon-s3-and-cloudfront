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
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Model;

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Group;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\UnexpectedValueException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\LogicException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\ToArrayInterface;
/**
 * Amazon S3 Grantee model
 */
class Grantee implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\ToArrayInterface
{
    /**
     * @var array A map of grantee types to grant header value prefixes
     */
    protected static $headerMap = array(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::USER => 'id', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::EMAIL => 'emailAddress', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::GROUP => 'uri');
    /**
     * @var string The account ID, email, or URL identifying the grantee
     */
    protected $id;
    /**
     * @var string The display name of the grantee
     */
    protected $displayName;
    /**
     * @var string The type of the grantee (CanonicalUser or Group)
     */
    protected $type;
    /**
     * Constructs a Grantee
     *
     * @param string $id           Grantee identifier
     * @param string $displayName  Grantee display name
     * @param string $expectedType The expected type of the grantee
     */
    public function __construct($id, $displayName = null, $expectedType = null)
    {
        $this->type = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::USER;
        $this->setId($id, $expectedType);
        $this->setDisplayName($displayName);
    }
    /**
     * Sets the account ID, email, or URL identifying the grantee
     *
     * @param string $id           Grantee identifier
     * @param string $expectedType The expected type of the grantee
     *
     * @return Grantee
     *
     * @throws UnexpectedValueException if $expectedType is set and the grantee
     *     is not of that type after instantiation
     * @throws InvalidArgumentException when the ID provided is not a string
     */
    public function setId($id, $expectedType = null)
    {
        if (in_array($id, \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Group::values())) {
            $this->type = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::GROUP;
        } elseif (!is_string($id)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException('The grantee ID must be provided as a string value.');
        }
        if (strpos($id, '@') !== false) {
            $this->type = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::EMAIL;
        }
        if ($expectedType && $expectedType !== $this->type) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\UnexpectedValueException('The type of the grantee after ' . 'setting the ID did not match the specified, expected type "' . $expectedType . '" but received "' . $this->type . '".');
        }
        $this->id = $id;
        return $this;
    }
    /**
     * Gets the grantee identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Gets the grantee email address (if it is set)
     *
     * @return null|string
     */
    public function getEmailAddress()
    {
        return $this->isAmazonCustomerByEmail() ? $this->id : null;
    }
    /**
     * Gets the grantee URI (if it is set)
     *
     * @return null|string
     */
    public function getGroupUri()
    {
        return $this->isGroup() ? $this->id : null;
    }
    /**
     * Sets the display name of the grantee
     *
     * @param string $displayName Grantee name
     *
     * @return Grantee
     *
     * @throws LogicException when the grantee type not CanonicalUser
     */
    public function setDisplayName($displayName)
    {
        if ($this->type === \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::USER) {
            if (empty($displayName) || !is_string($displayName)) {
                $displayName = $this->id;
            }
            $this->displayName = $displayName;
        } else {
            if ($displayName) {
                throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\LogicException('The display name can only be set ' . 'for grantees specified by ID.');
            }
        }
        return $this;
    }
    /**
     * Gets the grantee display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }
    /**
     * Gets the grantee type (determined by ID)
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * Returns true if this grantee object represents a canonical user by ID
     *
     * @return bool
     */
    public function isCanonicalUser()
    {
        return $this->type === \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::USER;
    }
    /**
     * Returns true if this grantee object represents a customer by email
     *
     * @return bool
     */
    public function isAmazonCustomerByEmail()
    {
        return $this->type === \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::EMAIL;
    }
    /**
     * Returns true if this grantee object represents a group by URL
     *
     * @return bool
     */
    public function isGroup()
    {
        return $this->type === \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::GROUP;
    }
    /**
     * Returns the value used in headers to specify this grantee
     *
     * @return string
     */
    public function getHeaderValue()
    {
        $key = static::$headerMap[$this->type];
        return "{$key}=\"{$this->id}\"";
    }
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = array('Type' => $this->type);
        switch ($this->type) {
            case \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::USER:
                $result['ID'] = $this->id;
                $result['DisplayName'] = $this->displayName;
                break;
            case \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::EMAIL:
                $result['EmailAddress'] = $this->id;
                break;
            case \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\GranteeType::GROUP:
                $result['URI'] = $this->id;
        }
        return $result;
    }
}
