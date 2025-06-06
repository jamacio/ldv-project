<?php
namespace Ldv\AdvancedPermissions\Controller\Adminhtml\Role\Productsgrid;

/**
 * Interceptor class for @see \Ldv\AdvancedPermissions\Controller\Adminhtml\Role\Productsgrid
 */
class Interceptor extends \Ldv\AdvancedPermissions\Controller\Adminhtml\Role\Productsgrid implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Authorization\Model\RoleFactory $roleFactory, \Magento\User\Model\UserFactory $userFactory, \Magento\Authorization\Model\RulesFactory $rulesFactory, \Magento\Backend\Model\Auth\Session $authSession, \Magento\Framework\Filter\FilterManager $filterManager, \Ldv\AdvancedPermissions\Block\Adminhtml\Role\Grid\Product $gridProducts)
    {
        $this->___init();
        parent::__construct($context, $coreRegistry, $roleFactory, $userFactory, $rulesFactory, $authSession, $filterManager, $gridProducts);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
