<?php

namespace Ldv\AdvancedPermissions\Preference\Search\Dashboard;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Search\Block\Adminhtml\Dashboard\Last;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;

/**
 * Class LastPreference
 *
 * @package Ldv\AdvancedPermissions\Preference\Search\Dashboard
 */
class LastPreference extends Last
{
    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * LastPreference constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Manager $moduleManager
     * @param CollectionFactory $queriesFactory
     * @param UserConfig $userConfig
     * @param ModuleConfig $moduleConfig
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleScopeFactory $roleScopeFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Manager $moduleManager,
        CollectionFactory $queriesFactory,
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $moduleManager,
            $queriesFactory,
            $data
        );
        $this->userConfig = $userConfig;
        $this->moduleConfig = $moduleConfig;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareCollection()
    {
        $allowedStoreIds = $this->getAllowedStoreIds();
        if ($allowedStoreIds === false) {
            return parent::_prepareCollection();
        }
        $this->_collection = $this->_queriesFactory->create();
        $this->_collection->setRecentQueryFilter();
        if ($allowedStoreIds === []) {
            $this->_collection->addFieldToFilter('store_id', ['in' => $allowedStoreIds]);
            return $this;
        }

        $storeIds = [];
        if ($this->getRequest()->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getRequest()->getParam('store'));
            return parent::_prepareCollection();
        } elseif ($this->getRequest()->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } elseif ($this->getRequest()->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        $storeIds = array_unique(array_merge($allowedStoreIds, $storeIds));
        $this->_collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        $this->setCollection($this->_collection);

        return $this;
    }

    /**
     * @return array|bool
     */
    private function getAllowedStoreIds()
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
        $storesList = $this->_storeManager->getStores(true, false);
        $storeIds = [];

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            foreach ($storesList as $store) {
                $storeIds[$store->getWebsiteId()][] = $store->getId();
            }
            $allowedWebsites = $scopeRole->getReferenceValue();
            if ($allowedWebsites === '' || $allowedWebsites === null) {
                return $storeIds;
            }
            $allowedIds = explode(',', $allowedWebsites);
            $allowedStoreIds = [];
            foreach ($allowedIds as $website) {
                if (!empty($storeIds[$website])) {
                    $allowedStoreIds = array_merge($allowedStoreIds, $storeIds[$website]);
                }
            }
            return $allowedStoreIds;
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            foreach ($storesList as $store) {
                $storeIds[] = $store->getId();
            }
            $allowedStores = $scopeRole->getReferenceValue();
            if ($allowedStores === '' || $allowedStores === null) {
                return $storeIds;
            }
            $allowedIds = explode(',', $allowedStores);
            $allowedStoreIds = [];
            foreach ($allowedIds as $storeId) {
                if (!in_array($storeId, $storeIds)) {
                    continue;
                }
                $allowedStoreIds[] = $storeId;
            }
            return $allowedStoreIds;
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
