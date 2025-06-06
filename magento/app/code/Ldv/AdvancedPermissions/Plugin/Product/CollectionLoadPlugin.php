<?php

namespace Ldv\AdvancedPermissions\Plugin\Product;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\RoleScope;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\RoleProductFactory;
use Ldv\AdvancedPermissions\Model\RoleCategoryFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class CollectionLoadPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Product
 */
class CollectionLoadPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RoleProductFactory
     */
    private $roleProductFactory;

    /**
     * @var RoleCategoryFactory
     */
    private $roleCategoryFactory;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * CollectionLoadPlugin constructor.
     *
     * @param UserConfig                $userConfig
     * @param ModuleConfig              $moduleConfig
     * @param ManagerInterface          $messageManager
     * @param ResultFactory             $resultFactory
     * @param LayoutFactory             $layoutFactory
     * @param JsonFactory               $resultJsonFactory
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RoleProductFactory        $roleProductFactory
     * @param RoleCategoryFactory       $roleCategoryFactory
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
        RoleProductFactory $roleProductFactory,
        RoleCategoryFactory $roleCategoryFactory,
        RoleScopeFactory $roleScopeFactory
    ) {
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->roleProductFactory = $roleProductFactory;
        $this->roleCategoryFactory = $roleCategoryFactory;
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
     * Filter products collection.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundLoad(\Magento\Catalog\Model\ResourceModel\Product\Collection $subject, callable $proceed)
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

        if (
            $advancedPermission->getIsProductLimit() == false
            && $advancedPermission->getIsCategoryLimit() == false
            && $advancedPermission->getIsScopeLimit() == false
        ) {
            return $proceed();
        }

        // filter by websites or stores
        $roleScope = $this->roleScopeFactory->create()->load($role->getId());
        if (
            $advancedPermission->getIsScopeLimit()
            && $roleScope !== null
            && $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES
        ) {
            if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                $allowedWebsiteIds = [];
            } else {
                $allowedWebsiteIds = explode(',', $roleScope->getReferenceValue());
            }

            $subject->addWebsiteFilter($allowedWebsiteIds);
        }

        // filter by categories
        $roleCategory = $this->roleCategoryFactory->create()->load($role->getId());
        if ($advancedPermission->getIsCategoryLimit() && $roleCategory !== null) {
            if ($roleCategory->getReferenceValue() === '' || $roleCategory->getReferenceValue() === null) {
                $allowedCategoryIds = [];
            } else {
                $allowedCategoryIds = explode(',', $roleCategory->getReferenceValue());
            }

            $subject->addCategoriesFilter(['in' => $allowedCategoryIds]);
        }

        // filter by products
        $roleProduct = $this->roleProductFactory->create()->load($role->getId());
        if (
            $advancedPermission->getIsProductLimit()
            && $roleProduct !== null
            && $roleProduct->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_PRODUCTS
        ) {
            // filter by specified products
            if ($roleProduct->getReferenceValue() === '' || $roleProduct->getReferenceValue() === null) {
                $allowedProductIds = [];
            } else {
                $allowedProductIds = explode(',', $roleProduct->getReferenceValue());
            }

            $subject->addAttributeToFilter('entity_id', ['in' => $allowedProductIds]);
        } elseif (
            $advancedPermission->getIsProductLimit()
            && $roleProduct !== null
            && $roleProduct->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_OWN_CREATED_PRODUCTS
        ) {
            // filter by own created products
            $userId = $this->userConfig->currentUser->getId();
            $subject->addFilter('e.owner_user_id', $userId);
        }

        return $proceed();
    }
}
