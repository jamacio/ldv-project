<?php
namespace Magento\Store\Model\System\Store;

/**
 * Interceptor class for @see \Magento\Store\Model\System\Store
 */
class Interceptor extends \Magento\Store\Model\System\Store implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->___init();
        parent::__construct($storeManager);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreCollection()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getStoreCollection');
        return $pluginInfo ? $this->___callPlugins('getStoreCollection', func_get_args(), $pluginInfo) : parent::getStoreCollection();
    }
}
