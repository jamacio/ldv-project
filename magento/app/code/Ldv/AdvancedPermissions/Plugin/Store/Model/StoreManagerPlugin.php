<?php

namespace Ldv\AdvancedPermissions\Plugin\Store\Model;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreManagerPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Store
 */
class StoreManagerPlugin extends AbstractPlugin
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
     * @param StoreManagerInterface $subject
     * @param $result
     * @return array
     */
    public function afterGetWebsites(
        StoreManagerInterface $subject,
        $result
    ) {
        if ($this->checkIsModuleEnabled() === false) {
            return $result;
        }

        if (empty($this->userConfig->currentUser)) {
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

            foreach ($result as $website) {
                $websiteId = $website->getWebsiteId();
                if (in_array($websiteId, $allowedWebsites) == false) {
                    unset($result[$websiteId]);
                }
            }
        }

        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStoreViews = $scopeRole->getReferenceValue();
            if ($allowedStoreViews === '' || $allowedStoreViews === null) {
                return [];
            }

            $allowedStoreViews = explode(',', $allowedStoreViews);
            $allowedWebsiteIds = [];
            foreach ($allowedStoreViews as $store) {
                try {
                    $allowedWebsiteIds[] = $subject->getStore($store)->getWebsiteId();
                } catch (NoSuchEntityException $entityException) {
                    continue;
                }
            }
            foreach ($result as $website) {
                $websiteId = $website->getWebsiteId();
                if (in_array($websiteId, $allowedWebsiteIds) == false) {
                    unset($result[$websiteId]);
                }
            }
        }

        return $result;
    }
}
