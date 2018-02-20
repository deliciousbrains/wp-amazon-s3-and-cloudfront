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

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\ToArrayInterface;
/**
 * Amazon S3 Grant model
 */
class Grant implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\ToArrayInterface
{
    /**
     * @var array A map of permissions to operation parameters
     */
    protected static $parameterMap = array(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::READ => 'GrantRead', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::WRITE => 'GrantWrite', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::READ_ACP => 'GrantReadACP', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::WRITE_ACP => 'GrantWriteACP', \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::FULL_CONTROL => 'GrantFullControl');
    /**
     * @var Grantee The grantee affected by the grant
     */
    protected $grantee;
    /**
     * @var string The permission set by the grant
     */
    protected $permission;
    /**
     * Constructs an ACL
     *
     * @param Grantee $grantee    Affected grantee
     * @param string  $permission Permission applied
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Model\Grantee $grantee, $permission)
    {
        $this->setGrantee($grantee);
        $this->setPermission($permission);
    }
    /**
     * Set the grantee affected by the grant
     *
     * @param Grantee $grantee Affected grantee
     *
     * @return $this
     */
    public function setGrantee(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Model\Grantee $grantee)
    {
        $this->grantee = $grantee;
        return $this;
    }
    /**
     * Get the grantee affected by the grant
     *
     * @return Grantee
     */
    public function getGrantee()
    {
        return $this->grantee;
    }
    /**
     * Set the permission set by the grant
     *
     * @param string $permission Permission applied
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setPermission($permission)
    {
        $valid = \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Enum\Permission::values();
        if (!in_array($permission, $valid)) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\InvalidArgumentException('The permission must be one of ' . 'the following: ' . implode(', ', $valid) . '.');
        }
        $this->permission = $permission;
        return $this;
    }
    /**
     * Get the permission set by the grant
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }
    /**
     * Returns an array of the operation parameter and value to set on the operation
     *
     * @return array
     */
    public function getParameterArray()
    {
        return array(self::$parameterMap[$this->permission] => $this->grantee->getHeaderValue());
    }
    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array('Grantee' => $this->grantee->toArray(), 'Permission' => $this->permission);
    }
}
