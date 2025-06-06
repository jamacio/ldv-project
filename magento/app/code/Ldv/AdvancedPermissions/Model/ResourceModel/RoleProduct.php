<?php

namespace Ldv\AdvancedPermissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class RoleProduct
 *
 * @package Ldv\AdvancedPermissions\Model\ResourceModel
 */
class RoleProduct extends AbstractDb
{
    protected $_isPkAutoIncrement = false;
    /**
     * RoleScope ResourceModel initialization.
     */
    protected function _construct()
    {
        $this->_init('ldv_advancedpermissions_role_product', 'role_id');
    }
}
