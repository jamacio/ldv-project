<?php

namespace Ldv\AdvancedPermissions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AdvancedPermission
 *
 * @package Ldv\AdvancedPermissions\Model\ResourceModel
 */
class AdvancedPermission extends AbstractDb
{
    protected $_isPkAutoIncrement = false;

    /**
     * AdvancedPermission ResourceModel initialization.
     */
    protected function _construct()
    {
        $this->_init('ldv_advancedpermissions_role', 'role_id');
    }
}
