<?php

namespace Ldv\AdvancedPermissions\Plugin\Cms;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Magento\Cms\Model\ResourceModel\Block\Collection;

/**
 * Class CollectionLoadPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Cms
 */
class CollectionLoadPlugin extends Collection
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
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

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
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
        $this->moduleConfig = $moduleConfig;
        $this->userConfig = $userConfig;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $filter = $this->filterPermission();
        if (false === $filter) {
            return parent::toOptionArray();
        }
        $filter[] = '0';
        $res = [];
        foreach ($this as $item) {
            if (!empty($item['store_id'])) {
                $containsSearch = count(array_intersect($item['store_id'], $filter)) == count($item['store_id']);
                if ($containsSearch) {
                    if (empty($idFieldName)) {
                        $res[] = ['value' => $item->getBlockId(), 'label' => $item->getTitle()];
                    }
                }
            }
        }
        return $res;
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
        $storeIds[] = [0 => '0'];

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
