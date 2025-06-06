<?php
namespace PagBank\PaymentMagento\Controller\Customer\SaveCard;

/**
 * Interceptor class for @see \PagBank\PaymentMagento\Controller\Customer\SaveCard
 */
class Interceptor extends \PagBank\PaymentMagento\Controller\Customer\SaveCard implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \PagBank\PaymentMagento\Api\PagBankVaultManagementInterface $vaultManagement, \Magento\Framework\Controller\Result\JsonFactory $jsonFactory, \Magento\Customer\Model\Session $customerSession)
    {
        $this->___init();
        parent::__construct($context, $vaultManagement, $jsonFactory, $customerSession);
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
