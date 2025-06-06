<?php

namespace Ldv\AdvancedPermissions\Model;

use Ldv\AdvancedPermissions\Api\RoleCategoryInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class RoleCategory
 *
 * @package Ldv\AdvancedPermissions\Model
 */
class RoleCategory extends AbstractModel implements IdentityInterface, RoleCategoryInterface
{
    /**
     * Cache tag.
     *
     * @const string
     */
    const CACHE_TAG = 'ldv_advancedpermissions_role_category';

    /**
     * RoleCategory Model initialization.
     */
    protected function _construct()
    {
        $this->_cacheTag = 'ldv_advancedpermissions_role_category';
        $this->_eventPrefix = 'ldv_advancedpermissions_role_category';

        $this->_init(\Ldv\AdvancedPermissions\Model\ResourceModel\RoleCategory::class);
    }

    /**
     * Return unique ID(s) for each object in system.
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get role id.
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * Set role id.
     *
     * @param $roleId
     */
    public function setRoleId($roleId)
    {
        return $this->setData(self::ROLE_ID, $roleId);
    }

    /**
     * Get reference value.
     *
     * @return int
     */
    public function getReferenceValue()
    {
        return $this->getData(self::REFERENCE_VALUE);
    }

    /**
     * Set reference value.
     *
     * @param $referenceValue
     *
     * @return mixed
     */
    public function setReferenceValue($referenceValue)
    {
        return $this->setData(self::REFERENCE_VALUE, $referenceValue);
    }
}
