<?php

namespace Ldv\AdvancedPermissions\Plugin\Product;

use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class MassDeletePlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Product
 */
class MassDeletePlugin extends AbstractPlugin
{
    const REDIRECT_PATH = 'catalog/product/index';

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\MassDelete $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Product\MassDelete $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::PRODUCT_DELETE_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to delete product.')
        );
    }
}
