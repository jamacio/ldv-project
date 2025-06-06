<?php

namespace Ldv\AdvancedPermissions\Model\ResourceModel\RoleCategory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Ldv\AdvancedPermissions\Model\ResourceModel\RoleCategory
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'role_id';

    /**
     * Id field name
     */
    protected function _construct()
    {
        $this->_init(
            \Ldv\AdvancedPermissions\Model\RoleCategory::class,
            \Ldv\AdvancedPermissions\Model\ResourceModel\RoleCategory::class
        );
    }

    /**
     * Collection initialization.
     */
    protected function _initSelect()
    {
        parent::_initSelect();
    }
}
