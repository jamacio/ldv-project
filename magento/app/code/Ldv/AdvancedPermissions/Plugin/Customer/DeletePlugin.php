<?php

namespace Ldv\AdvancedPermissions\Plugin\Customer;

use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class EditPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Customer
 */
class DeletePlugin extends AbstractPlugin
{
    const REDIRECT_PATH = 'customer/index/index';

    /**
     * @param \Magento\Customer\Controller\Adminhtml\Index\Edit $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Customer\Controller\Adminhtml\Index\Delete $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CUSTOMER_DELETE_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to delete customer.')
        );
    }
}
