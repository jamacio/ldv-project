<?php

namespace Ldv\AdvancedPermissions\Plugin\Category;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\RoleCategoryFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class FilterCategoryPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Category
 */
class FilterCategoryPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * @var UserConfig
     */
    protected $userConfig;

    /**
     * @var RoleScopeFactory
     */
    protected $roleScopeFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * FilterCategoryPlugin constructor.
     * @param UserConfig $userConfig
     * @param ModuleConfig $moduleConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleCategoryFactory $roleCategoryFactory
     * @param StoreManagerInterface $storeManager
     * @param RoleScopeFactory $roleScopeFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RoleCategoryFactory $roleCategoryFactory,
        StoreManagerInterface $storeManager,
        RoleScopeFactory $roleScopeFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleCategoryFactory = $roleCategoryFactory;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;

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
     * Filter all category collections to allowed category ids.
     *
     * @param Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundLoad(Collection $subject, callable $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        if ($this->userConfig->currentUser === null) {
            return $proceed();
        }

        $role = $this->userConfig->currentUser->getRole();
        $advancedPermission = $this->advancedPermissionFactory->create()->load($role->getId());

        if ($advancedPermission === null) {
            return $proceed();
        }
        if ($advancedPermission->getIsScopeLimit() != false) {
            $scopeRole = $this->roleScopeFactory->create()->load($role->getId());
            $scopePermissions = $this->filterPermission($scopeRole);
            if (!empty($scopePermissions)) {
                if (!empty($scopePermissions)) {
                    $subject->addAttributeToFilter(
                        'path',
                        $scopePermissions
                    );
                }
            }
        }

        if ($advancedPermission->getIsCategoryLimit() == false) {
            return $proceed();
        }

        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());

        if ($roleCategory === null) {
            return $proceed();
        }

        if ($roleCategory->getReferenceValue() === '' || $roleCategory->getReferenceValue() === null) {
            $allowedCategoryIds = null;
        } else {
            $allowedCategoryIds = explode(',', $roleCategory->getReferenceValue());
        }

        $subject->addAttributeToFilter('entity_id', ['in' => $allowedCategoryIds]);

        return $proceed();
    }

    /**
     * @param $scopeRole
     * @return array|bool
     */
    private function filterPermission($scopeRole)
    {
        $allowedRootCategoryIds = [];
        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            $allowedWebsites = $scopeRole->getReferenceValue();
            if ($allowedWebsites === '' || $allowedWebsites === null) {
                return [];
            }
            $allowedWebsiteIds = explode(',', $allowedWebsites);
            foreach ($allowedWebsiteIds as $websiteId) {
                foreach ($this->storeManager->getWebsite($websiteId)->getStores() as $store) {
                    $allowedRootCategoryIds[] = $store->getRootCategoryId();
                }
            }
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStores = $scopeRole->getReferenceValue();
            if ($allowedStores === '' || $allowedStores === null) {
                return [];
            }
            $allowedIds = explode(',', $allowedStores);
            foreach ($allowedIds as $storeId) {
                $store = $this->storeManager->getStore($storeId);
                $allowedRootCategoryIds[] = $store->getRootCategoryId();
            }
        }
        if (empty($allowedRootCategoryIds)) {
            return false;
        }
        $pathFilter = [];
        foreach ($allowedRootCategoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $exception) {
                continue;
            }
            $pathFilter[] = ['like' => $category->getPath() . '%'];
        }
        return $pathFilter;
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
