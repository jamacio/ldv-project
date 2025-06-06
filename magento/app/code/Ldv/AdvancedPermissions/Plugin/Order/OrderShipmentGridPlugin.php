<?php

namespace Ldv\AdvancedPermissions\Plugin\Order;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderShipmentGridPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Order
 */
class OrderShipmentGridPlugin extends AbstractPlugin
{
    /**
     * @var AdvancedPermissionCollection
     */
    private $advancedPermissionCollection;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * OrderShipmentGridPlugin constructor.
     * @param UserConfig $userConfig
     * @param ModuleConfig $moduleConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param AdvancedPermissionCollection $advancedPermissionCollection
     * @param RoleScopeFactory $roleScopeFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionCollection $advancedPermissionCollection,
        RoleScopeFactory $roleScopeFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->storeManager = $storeManager;

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
     * Filter order shipment grid.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundGetItems(\Magento\Sales\Model\ResourceModel\Order\Shipment\Grid\Collection $subject, callable $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed();
        }

        if ($this->userConfig->currentUser === null) {
            return $proceed();
        }

        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($advancedPermissionCollection->count() > 0) {
            $advancedPermission = $advancedPermissionCollection->getFirstItem();

            if ($advancedPermission->getRoleId() != $roleId) {
                return $proceed();
            }

            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeFactory
                    ->create()
                    ->load($advancedPermission->getRoleId());

                if ($roleScope->getAccessLevel() != AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
                    if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                        $allowedStoresViews = [];
                    } else {
                        $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                    }

                    $subject->addFieldToFilter('store_id', ['in' => $allowedStoresViews]);
                } elseif ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
                    if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                        $allowedStoresViews = [];
                    } else {
                        $allowedWebsites = explode(',', $roleScope->getReferenceValue());
                        $allowedStoresViews = [];
                        foreach ($allowedWebsites as $websiteId) {
                            try {
                                $storesList = $this->storeManager->getWebsite($websiteId);
                            } catch (LocalizedException $exception) {
                                continue;
                            }
                            if (!empty($storesList->getStores())) {
                                foreach ($storesList->getStores() as $store) {
                                    $allowedStoresViews[] = $store->getId();
                                }
                            }
                        }
                    }
                    $subject->addFieldToFilter('store_id', ['in' => $allowedStoresViews]);
                }
                return $proceed();
            }
        }

        return $proceed();
    }
}
