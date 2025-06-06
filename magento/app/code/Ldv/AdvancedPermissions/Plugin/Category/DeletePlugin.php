<?php

namespace Ldv\AdvancedPermissions\Plugin\Category;

use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class DeletePlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Category
 */
class DeletePlugin extends AbstractPlugin
{
    /**
     *
     */
    const REDIRECT_PATH = 'catalog/category/index';

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Delete $subject
     * @param $proceed
     *
     * @return mixed
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Category\Delete $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CATEGORY_DELETE_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBack(
            self::REDIRECT_PATH,
            __('You do not have permission to delete category.')
        );
    }
}
