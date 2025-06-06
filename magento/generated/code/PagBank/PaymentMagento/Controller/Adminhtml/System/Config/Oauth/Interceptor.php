<?php
namespace PagBank\PaymentMagento\Controller\Adminhtml\System\Config\Oauth;

/**
 * Interceptor class for @see \PagBank\PaymentMagento\Controller\Adminhtml\System\Config\Oauth
 */
class Interceptor extends \PagBank\PaymentMagento\Controller\Adminhtml\System\Config\Oauth implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Serialize\Serializer\Json $json, \PagBank\PaymentMagento\Model\Api\Credential $credential)
    {
        $this->___init();
        parent::__construct($context, $cacheTypeList, $cacheFrontendPool, $resultJsonFactory, $storeManager, $json, $credential);
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
