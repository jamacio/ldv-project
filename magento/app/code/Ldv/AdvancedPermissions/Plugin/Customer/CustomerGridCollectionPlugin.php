<?php

namespace Ldv\AdvancedPermissions\Plugin\Customer;

use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\ResourceModel\AdvancedPermission\Collection as AdvancedPermissionCollection;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class CustomerGridCollectionPlugin
 *
 * @package Ldv\AdvancedPermissions\Plugin\Customer
 */
class CustomerGridCollectionPlugin extends AbstractPlugin
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
     * CustomerGridCollectionPlugin constructor.
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
     * Filter customer grid.
     *
     * @param \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundGetItems(\Magento\Customer\Model\ResourceModel\Grid\Collection $subject, callable $proceed)
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

                if ($roleScope->getReferenceValue() === '' || $roleScope->getReferenceValue() === null) {
                    $allowedStoresViews = [];
                } else {
                    $allowedStoresViews = explode(',', $roleScope->getReferenceValue());
                }


                if ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {

                    $joinTable = $subject->getTable('customer_entity');
                    $subject
                        ->getSelect()
                        ->join($joinTable, 'main_table.entity_id = customer_entity.entity_id', ['store_id']);
                    $subject->addFieldToFilter('customer_entity.website_id', ['in' => $allowedStoresViews]);
                } elseif ($roleScope->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {

                    $joinTable = $subject->getTable('customer_entity');
                    $subject
                        ->getSelect()
                        ->join($joinTable, 'main_table.entity_id = customer_entity.entity_id', ['store_id']);
                    $subject->addFieldToFilter('store_id', ['in' => $allowedStoresViews]);
                }

                return $proceed();
            }
        }

        return $proceed();
    }
}
