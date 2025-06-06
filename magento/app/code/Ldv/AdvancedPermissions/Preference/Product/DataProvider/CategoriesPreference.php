<?php

namespace Ldv\AdvancedPermissions\Preference\Product\DataProvider;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\Exception\LocalizedException;
use Ldv\AdvancedPermissions\Model\RoleCategoryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Overwrite category tree retrieve method to add website filter to collection
 *
 * @package Ldv\AdvancedPermissions\Preference\Product\DataProvider
 */
class CategoriesPreference extends Categories
{
    /**
     * @var CacheInterface
     */
    private $cacheManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * CategoriesPreference constructor.
     * @param LocatorInterface $locator
     * @param UserConfig $userConfig
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param DbHelper $dbHelper
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleScopeFactory $roleScopeFactory
     * @param ModuleConfig $moduleConfig
     * @param StoreManagerInterface $storeManager
     * @param RoleCategoryFactory $roleCategoryFactory
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        LocatorInterface $locator,
        UserConfig $userConfig,
        CategoryCollectionFactory $categoryCollectionFactory,
        DbHelper $dbHelper,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleScopeFactory $roleScopeFactory,
        ModuleConfig $moduleConfig,
        StoreManagerInterface $storeManager,
        RoleCategoryFactory $roleCategoryFactory,
        SerializerInterface $serializer = null
    ) {
        parent::__construct(
            $locator,
            $categoryCollectionFactory,
            $dbHelper,
            $urlBuilder,
            $arrayManager,
            $serializer
        );
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->moduleConfig = $moduleConfig;
        $this->userConfig = $userConfig;
        $this->storeManager = $storeManager;
        $this->roleCategoryFactory = $roleCategoryFactory;
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     * @throws LocalizedException
     * @since 101.0.0
     */
    protected function getCategoriesTree($filter = null)
    {
        $storeId = (int) $this->locator->getStore()->getId();

        /*$cachedCategoriesTree = $this->getCacheManager()
            ->load($this->getCategoriesTreeCacheId($storeId, (string) $filter));
        if (!empty($cachedCategoriesTree)) {
            return $this->serializer->unserialize($cachedCategoriesTree);
        }*/

        $categoriesTree = $this->retrieveCategoriesTree(
            $storeId,
            $this->retrieveShownCategoriesIds($storeId, (string) $filter)
        );

        $this->getCacheManager()->save(
            $this->serializer->serialize($categoriesTree),
            $this->getCategoriesTreeCacheId($storeId, (string) $filter),
            [
                CategoryModel::CACHE_TAG,
                \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
            ]
        );

        return $categoriesTree;
    }

    /**
     * Retrieve filtered list of categories id.
     *
     * @param int $storeId
     * @param string $filter
     * @return array
     * @throws LocalizedException
     */
    private function retrieveShownCategoriesIds(int $storeId, string $filter = ''): array
    {
        $matchingNamesCollection = $this->categoryCollectionFactory->create();

        if (!empty($filter)) {
            $matchingNamesCollection->addAttributeToFilter(
                'name',
                ['like' => $this->dbHelper->addLikeEscape($filter, ['position' => 'any'])]
            );
        }

        $matchingNamesCollection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
            ->setStoreId($storeId);

        $shownCategoriesIds = [];

        /** @var CategoryModel $category */
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        return $shownCategoriesIds;
    }

    /**
     * Get cache id for categories tree.
     *
     * @param int $storeId
     * @param string $filter
     * @return string
     */
    private function getCategoriesTreeCacheId(int $storeId, string $filter = ''): string
    {
        return self::CATEGORY_TREE_ID
            . '_' . (string) $storeId
            . '_' . $filter;
    }

    /**
     * Retrieve cache interface
     *
     * @return CacheInterface
     * @deprecated 101.0.3
     */
    private function getCacheManager()
    {
        if (!$this->cacheManager) {
            $this->cacheManager = ObjectManager::getInstance()
                ->get(CacheInterface::class);
        }
        return $this->cacheManager;
    }

    /**
     * Retrieve tree of categories with attributes.
     *
     * @param int $storeId
     * @param array $shownCategoriesIds
     * @return array|null|mixed
     * @throws LocalizedException
     */
    private function retrieveCategoriesTree(int $storeId, array $shownCategoriesIds)
    {
        $collection = $this->categoryCollectionFactory->create();
        $websiteFilter = $this->getCategoryPathArray();
        $categoryAccessFilter = $this->getCategoryAccessFilters();
        $shownCategoriesIds = array_keys($shownCategoriesIds);
        if (!empty($categoryAccessFilter)) {
            $shownCategoriesIds = $categoryAccessFilter;
        }
        $collection->addAttributeToFilter('entity_id', ['in' => $shownCategoriesIds])
            ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
            ->setStoreId($storeId);

        if (!empty($websiteFilter)) {
            $collection->addAttributeToFilter('path', $websiteFilter);
        }
        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getId()]['__disableTmpl'] = true;
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getCategoryPathArray(): array
    {
        $categoryIds = $this->getWebsiteCategoryFilter();
        if (empty($categoryIds)) {
            return [];
        }
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('path')
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
        $conditions = [];
        foreach ($collection as $category) {
            $conditions[] = ['like' => $category->getPath() . '%'];
        }
        return $conditions;
    }

    /**
     * Get root categories paths' for limited websites stores
     *
     * @return array
     */
    private function getWebsiteCategoryFilter(): array
    {
        $result = [];
        if ($this->moduleConfig->isActive() === false) {
            return $result;
        }

        $roleId = $this->userConfig->currentUser->getRole()->getId();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($roleId);

        if ($advancedPermission->getIsScopeLimit() == false) {
            return $result;
        }

        $scopeRole = $this->roleScopeFactory->create()->load($roleId);
        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            $allowedWebsites = $scopeRole->getReferenceValue();
            if ($allowedWebsites === '' || $allowedWebsites === null) {
                return [];
            }
            $allowedWebsites = explode(',', $allowedWebsites);
            foreach ($allowedWebsites as $websiteId) {
                try {
                    $website = $this->storeManager->getWebsite($websiteId);
                } catch (LocalizedException $entityException) {
                    continue;
                }
                if (!empty($website->getStores())) {
                    foreach ($website->getStores() as $store) {
                        $result[] = $store->getRootCategoryId();
                    }
                }
            }
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStoreViews = $scopeRole->getReferenceValue();
            if ($allowedStoreViews === '' || $allowedStoreViews === null) {
                return [];
            }

            $allowedStoreViews = explode(',', $allowedStoreViews);
            foreach ($allowedStoreViews as $store) {
                try {
                    $websiteId = $this->storeManager->getStore($store)->getWebsiteId();
                    $website = $this->storeManager->getWebsite($websiteId);
                } catch (NoSuchEntityException $entityException) {
                    continue;
                } catch (LocalizedException $exception) {
                    continue;
                }
                if (!empty($website->getStores())) {
                    foreach ($website->getStores() as $store) {
                        $result[] = $store->getRootCategoryId();
                    }
                }
            }
        }
        return array_unique($result);
    }

    /**
     * Get category ids from category scope filter if set
     *
     * @return array
     */
    private function getCategoryAccessFilters()
    {
        $result = [];
        if ($this->moduleConfig->isActive() === false) {
            return $result;
        }

        $roleId = $this->userConfig->currentUser->getRole()->getId();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($roleId);

        if ($advancedPermission->getIsCategoryLimit() == false) {
            return $result;
        }
        $roleCategory = $this->roleCategoryFactory->create()->load($roleId);
        if ($roleCategory !== null) {
            if ($roleCategory->getReferenceValue() !== '' || $roleCategory->getReferenceValue() !== null) {
                $result = explode(',', $roleCategory->getReferenceValue());
            }
        }
        return $result;
    }
}
