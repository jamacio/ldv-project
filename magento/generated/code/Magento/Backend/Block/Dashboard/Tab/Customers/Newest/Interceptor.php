<?php
namespace Magento\Backend\Block\Dashboard\Tab\Customers\Newest;

/**
 * Interceptor class for @see \Magento\Backend\Block\Dashboard\Tab\Customers\Newest
 */
class Interceptor extends \Magento\Backend\Block\Dashboard\Tab\Customers\Newest implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory $collectionFactory, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $backendHelper, $collectionFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCollection');
        return $pluginInfo ? $this->___callPlugins('getCollection', func_get_args(), $pluginInfo) : parent::getCollection();
    }
}
