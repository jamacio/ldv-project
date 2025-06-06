<?php

namespace Ldv\AdvancedPermissions\Preference\Cms\Block\Grid;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Cms\Model\ResourceModel\Block\Grid\Collection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CollectionPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Cms\Block\Grid
 */
class CollectionPreference extends Collection
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

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $mainTable,
            $eventPrefix,
            $eventObject,
            $resourceModel,
            $model,
            $connection,
            $resource
        );
        $this->userConfig = $userConfig;
        $this->moduleConfig = $moduleConfig;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
    }

    /**
     * @return $this|Collection
     */
    protected function _afterLoad()
    {
        $needFilter = $this->filterPermission();
        if ($needFilter === []) {
            $this->clear();
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
            return $this;
        }
        $result = parent::_afterLoad();
        if ($needFilter === false) {
            return $result;
        } else {
            array_push($needFilter, 0);
            $idFieldName = $this->getIdFieldName();
            $size = $result->getSize();
            foreach ($result as $key => $item) {
                if (!empty($item['store_id'])) {
                    $containsSearch = count(array_intersect($item['store_id'], $needFilter)) == count($item['store_id']);
                    if (!$containsSearch) {
                        if (!empty($idFieldName)) {
                            $result->removeItemByKey($key);
                            $size--;
                        }
                    }
                }
            }
            $this->_totalRecords = $size;
            $this->_setIsLoaded(true);
        }
        return $result;
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
        $storesList = $this->storeManager->getStores(true, false);
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
