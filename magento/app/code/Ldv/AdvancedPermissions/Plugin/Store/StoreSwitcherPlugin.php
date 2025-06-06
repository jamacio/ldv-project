<?php

namespace Ldv\AdvancedPermissions\Plugin\Store;

use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Api\RoleScopeInterface;
use Ldv\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class StoreSwitcherPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Store
 */
class StoreSwitcherPlugin extends AbstractPlugin
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
     * StoreSwitcherPlugin constructor.
     *
     * @param UserConfig                   $userConfig
     * @param ModuleConfig                 $moduleConfig
     * @param ManagerInterface             $messageManager
     * @param ResultFactory                $resultFactory
     * @param LayoutFactory                $layoutFactory
     * @param JsonFactory                  $resultJsonFactory
     * @param AdvancedPermissionCollection $advancedPermissionCollection
     * @param RoleScopeFactory             $roleScopeFactory
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        AdvancedPermissionCollection $advancedPermissionCollection,
        RoleScopeFactory $roleScopeFactory
    ) {
        $this->advancedPermissionCollection = $advancedPermissionCollection;
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
     * Filter store swicher.
     *
     * @param \Magento\Backend\Block\Store\Switcher $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundHasDefaultOption(\Magento\Backend\Block\Store\Switcher $subject, callable $proceed)
    {
        if ($this->checkIsModuleEnabled() === false) {
            return $proceed(true);
        }

        $role = $this->userConfig->currentUser->getRole();
        $roleId = $role->getId();

        $advancedPermissionCollection = $this->advancedPermissionCollection
            ->addFieldToFilter('role_id', $roleId);

        if ($advancedPermissionCollection->count() > 0) {
            $advancedPermission = $advancedPermissionCollection->getFirstItem();

            if ($advancedPermission->getRoleId() !== $roleId) {
                return $proceed(true);
            }

            if ($advancedPermission->getIsScopeLimit() == true) {
                $roleScope = $this->roleScopeFactory
                    ->create()
                    ->load($advancedPermission->getRoleId());

                if (
                    $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS
                    || $roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES
                ) {
                    return $proceed(false);
                }
            }
        }

        return $proceed(true);
    }
}
