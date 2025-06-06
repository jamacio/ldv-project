<?php

namespace Ldv\AdvancedPermissions\Plugin\PriceRules;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartPriceRulesCollectionPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\PriceRules
 */
class CartPriceRulesCollectionPlugin extends AbstractPlugin
{
    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * CatalogPriceRulesCollectionPlugin constructor.
     *
     * @param UserConfig $userConfig
     * @param ModuleConfig $moduleConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param RoleScopeFactory $roleScopeFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        RoleScopeFactory $roleScopeFactory,
        StoreManagerInterface $storeManagerInterface,
        AdvancedPermissionFactory $advancedPermissionFactory
    ) {
        parent::__construct(
            $userConfig,
            $moduleConfig,
            $messageManager,
            $resultFactory,
            $layoutFactory,
            $resultJsonFactory
        );
        $this->roleScopeFactory = $roleScopeFactory;
        $this->storeManager = $storeManagerInterface;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
    }

    public function aroundLoad(Collection $subject, callable $proceed)
    {
        $permissionsFilter = $this->filterPermission();
        if (false === $permissionsFilter) {
            return $proceed();
        }
        $subject->addWebsiteFilter($permissionsFilter);
        return $proceed();
    }

    /**
     * @return array|bool
     */
    private function filterPermission()
    {
        if ($this->checkIsModuleEnabled() === false) {
            return false;
        }

        $roleId = $this->userConfig->currentUser->getRole()->getId();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($roleId);

        if ($advancedPermission->getIsScopeLimit() == false) {
            return false;
        }

        $scopeRole = $this->roleScopeFactory->create()->load($roleId);

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            $allowedWebsites = $scopeRole->getReferenceValue();
            $allowedIds = [];
            if ($allowedWebsites === '' || $allowedWebsites === null) {
                return $allowedIds;
            }
            return explode(',', $allowedWebsites);
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStores = $scopeRole->getReferenceValue();
            if ($allowedStores === '' || $allowedStores === null) {
                return [];
            }
            $allowedIds = explode(',', $allowedStores);
            $allowedWebsiteIds = [];
            $storesList = $this->storeManager->getStores(true, false);
            $storeIds = [];
            foreach ($storesList as $store) {
                $storeIds[$store->getWebsiteId()][] = $store->getId();
            }
            if (empty($storeIds)) {
                return false;
            }
            foreach ($storeIds as $websiteId => $storeIdsList) {
                foreach ($allowedIds as $storeId) {
                    if (in_array($storeId, $storeIdsList)) {
                        $allowedWebsiteIds[] = $websiteId;
                    }
                }
            }
            return array_unique($allowedWebsiteIds);
        }
        return false;
    }

    /**
     * Check is module enabled.
     *
     * @return bool
     */
    protected function checkIsModuleEnabled()
    {
        return $this->moduleConfig->isActive();
    }
}
