<?php
namespace Ldv\AdvancedPermissions\Block\Adminhtml\Role\Tab\Category;

/**
 * Interceptor class for @see \Ldv\AdvancedPermissions\Block\Adminhtml\Role\Tab\Category
 */
class Interceptor extends \Ldv\AdvancedPermissions\Block\Adminhtml\Role\Tab\Category implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Ldv\AdvancedPermissions\Model\User\Config $userConfig, \Ldv\AdvancedPermissions\Model\Config $moduleConfig, \Ldv\AdvancedPermissions\Model\AdvancedPermission $advancedPermission, \Ldv\AdvancedPermissions\Model\RoleCategory $roleCategory, \Magento\Catalog\Block\Adminhtml\Category\Tree $adminCategoryTree, \Ldv\AdvancedPermissions\Helper\Data $dataHelper, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $userConfig, $moduleConfig, $advancedPermission, $roleCategory, $adminCategoryTree, $dataHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getForm');
        return $pluginInfo ? $this->___callPlugins('getForm', func_get_args(), $pluginInfo) : parent::getForm();
    }
}
