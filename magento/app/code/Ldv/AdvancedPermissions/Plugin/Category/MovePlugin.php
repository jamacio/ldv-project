<?php

namespace Ldv\AdvancedPermissions\Plugin\Category;

use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;

/**
 * Class MovePlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Category
 */
class MovePlugin extends AbstractPlugin
{
    const REDIRECT_PATH = 'catalog/category/index';

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Move $subject
     * @param $proceed
     *
     * @return $this
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Category\Move $subject, $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        $resourcesToCheck = $this->moduleConfig->getResourcesToCheck(
            $this->moduleConfig::CATEGORY_CREATE_EDIT_ALLOWED_RESOURCES
        );

        if ($this->checkPermission($resourcesToCheck)) {
            return $proceed();
        }

        return $this->redirectBackAjax(
            __('You do not have permission to move category.')
        );
    }
}
