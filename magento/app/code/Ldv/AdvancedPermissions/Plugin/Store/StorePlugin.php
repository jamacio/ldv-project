<?php

namespace Ldv\AdvancedPermissions\Plugin\Store;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class StorePlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Store
 */
class StorePlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * StorePlugin constructor.
     *
     * @param UserConfig                $userConfig
     * @param ModuleConfig              $moduleConfig
     * @param ManagerInterface          $messageManager
     * @param ResultFactory             $resultFactory
     * @param LayoutFactory             $layoutFactory
     * @param JsonFactory               $resultJsonFactory
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleScopeFactory          $roleScopeFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;

        parent::__construct(
            $userConfig,
            $moduleConfig,
            $messageManager,
            $resultFactory,
            $layoutFactory,
            $resultJsonFactory
        );
    }

    /**
     * @param \Magento\Store\Model\System\Store $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetStoreCollection(\Magento\Store\Model\System\Store $subject, callable $proceed)
    {
        $stores = $proceed();

        if ($this->checkIsModuleEnabled() === false) {
            return $stores;
        }

        $roleId = $this->userConfig->currentUser->getRole()->getId();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($roleId);

        if ($advancedPermission->getIsScopeLimit() == false) {
            return $stores;
        }

        $scopeRole = $this->roleScopeFactory->create()->load($roleId);

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStoreViews = $scopeRole->getReferenceValue();
            if ($allowedStoreViews === '' || $allowedStoreViews === null) {
                return [];
            }

            $allowedStoreViews = explode(',', $allowedStoreViews);

            foreach ($stores as $store) {
                $storeId = $store->getStoreId();
                if (in_array($storeId, $allowedStoreViews) == false) {
                    unset($stores[$storeId]);
                }
            }

            return $stores;
        }

        return $stores;
    }
}
