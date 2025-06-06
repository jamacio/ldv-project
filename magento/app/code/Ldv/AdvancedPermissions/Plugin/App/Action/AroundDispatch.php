<?php

namespace Ldv\AdvancedPermissions\Plugin\App\Action;

use Closure;
use Ldv\AdvancedPermissions\Api\AdvancedPermissionInterface;
use Ldv\AdvancedPermissions\Model\AdvancedPermissionFactory;
use Ldv\AdvancedPermissions\Model\Config as ModuleConfig;
use Ldv\AdvancedPermissions\Model\RoleScopeFactory;
use Ldv\AdvancedPermissions\Model\User\Config as UserConfig;
use Ldv\AdvancedPermissions\Plugin\AbstractPlugin;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AroundDispatch extends AbstractPlugin
{
    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var BackendAppList
     */
    protected $backendAppList;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var RoleScopeFactory
     */
    private $roleScopeFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AdvancedPermissionFactory
     */
    private $advancedPermissionFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * AroundDispatch constructor.
     * @param UserConfig $userConfig
     * @param ModuleConfig $moduleConfig
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param LayoutFactory $layoutFactory
     * @param JsonFactory $resultJsonFactory
     * @param Auth $auth
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param ActionFlag $actionFlag
     * @param UrlInterface $backendUrl
     * @param RedirectFactory $resultRedirectFactory
     * @param BackendAppList $backendAppList
     * @param Validator $formKeyValidator
     * @param RoleScopeFactory $roleScopeFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param AdvancedPermissionFactory $advancedPermissionFactory
     * @param RedirectInterface $redirect
     */
    public function __construct(
        UserConfig $userConfig,
        ModuleConfig $moduleConfig,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        Auth $auth,
        UrlInterface $url,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        UrlInterface $backendUrl,
        RedirectFactory $resultRedirectFactory,
        BackendAppList $backendAppList,
        Validator $formKeyValidator,
        RoleScopeFactory $roleScopeFactory,
        StoreManagerInterface $storeManagerInterface,
        AdvancedPermissionFactory $advancedPermissionFactory,
        RedirectInterface $redirect
    ) {
        parent::__construct(
            $userConfig,
            $moduleConfig,
            $messageManager,
            $resultFactory,
            $layoutFactory,
            $resultJsonFactory
        );
        $this->_auth = $auth;
        $this->_url = $url;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->backendUrl = $backendUrl;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->backendAppList = $backendAppList;
        $this->formKeyValidator = $formKeyValidator;
        $this->roleScopeFactory = $roleScopeFactory;
        $this->storeManager = $storeManagerInterface;
        $this->advancedPermissionFactory = $advancedPermissionFactory;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * Prevent admin user from saving data on store view or website that he is not allowed to edit
     *
     * @param AbstractAction $subject
     * @param Closure $proceed
     * @param RequestInterface $request
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        AbstractAction $subject,
        Closure $proceed,
        RequestInterface $request
    ) {
        $action = $request->getActionName();
        $controller = $request->getControllerName();
        if ($action == 'save') {
            if ($controller == 'system_config' || !empty($request->getParam('id'))) {
                $filterPermissions = $this->filterPermission();
                if ($filterPermissions === false) {
                    return $proceed($request);
                }
                $referer = $this->redirect->getRefererUrl();
                $resultRedirect = $this->resultRedirectFactory->create();
                if (empty($filterPermissions)) {
                    $this->messageManager->addErrorMessage(__('You do not have permissions to edit this data.'));
                    $resultRedirect->setUrl($referer);
                    return $resultRedirect;
                }
                $permissionError = false;
                $website = $request->getParam('website');
                $store = $request->getParam('store');
                $storeId = $request->getParam('store_id');
                if ($filterPermissions['type'] == 'website') {
                    if (empty($website) && empty($store) && empty($storeId)) {
                        $permissionError = true;
                    } else {
                        if (
                            !empty($website)
                            && (empty($filterPermissions['website_ids'])
                                || !in_array($website, $filterPermissions['website_ids']))
                        ) {
                            $permissionError = true;
                        } elseif (
                            !empty($store)
                            && (empty($filterPermissions['store_ids'])
                                || !in_array($store, $filterPermissions['store_ids']))
                        ) {
                            $permissionError = true;
                        } elseif (
                            !empty($storeId)
                            && (empty($filterPermissions['store_ids'])
                                || !in_array($storeId, $filterPermissions['store_ids']))
                        ) {
                            $permissionError = true;
                        }
                    }
                } elseif ($filterPermissions['type'] == 'store') {
                    if (
                        !in_array($storeId, $filterPermissions['store_ids'])
                        && !in_array($store, $filterPermissions['store_ids'])
                    ) {
                        $permissionError = true;
                    }
                }

                if ($permissionError) {
                    $this->messageManager->addErrorMessage(
                        __('You do not have permissions to edit data on this data.
                     Please select corresponding website or store view to proceed.')
                    );
                    $resultRedirect->setUrl($referer);
                    return $resultRedirect;
                }
            }
        }

        return $proceed($request);
    }

    /**
     * @return array|bool
     */
    private function filterPermission()
    {
        if ($this->checkIsModuleEnabled() === false) {
            return false;
        }

        if (empty($this->userConfig->currentUser) || empty($this->userConfig->currentUser->getRole())) {
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
        $allowedStoreId = [];
        foreach ($storesList as $store) {
            $storeIds[$store->getWebsiteId()][] = $store->getId();
        }
        if ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_WEBSITES) {
            $allowedWebsites = $scopeRole->getReferenceValue();
            $allowedIds = [];
            if ($allowedWebsites === '' || $allowedWebsites === null) {
                return $allowedIds;
            }

            $websiteIds = explode(',', $allowedWebsites);
            $allowedWebsiteIds = [];

            foreach ($storeIds as $websiteId => $websiteStoreIds) {
                if (in_array($websiteId, $websiteIds)) {
                    $allowedStoreId = array_merge($allowedStoreId, $websiteStoreIds);
                    $allowedWebsiteIds[] = $websiteId;
                }
            }
            return [
                'type' => 'website',
                'store_ids' => array_unique($allowedStoreId),
                'website_ids' => $allowedWebsiteIds
            ];
        } elseif ($scopeRole->getAccessLevel() == AdvancedPermissionInterface::ACCESS_TO_SPECIFIED_STORE_VIEWS) {
            $allowedStores = $scopeRole->getReferenceValue();
            if ($allowedStores === '' || $allowedStores === null) {
                return [];
            }
            $allowedIds = explode(',', $allowedStores);
            $allowedStoreIds = [];
            if (empty($storeIds)) {
                return false;
            }
            foreach ($storeIds as $websiteId => $storesList) {
                foreach ($allowedIds as $storeId) {
                    if (in_array($storeId, $storesList)) {
                        $allowedStoreIds[] = $storeId;
                    }
                }
            }
            return [
                'type' => 'store',
                'store_ids' => array_unique($allowedStoreIds),
                'website_ids' => []
            ];
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
