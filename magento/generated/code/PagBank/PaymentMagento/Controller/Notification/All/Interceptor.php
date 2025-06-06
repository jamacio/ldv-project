<?php
namespace PagBank\PaymentMagento\Controller\Notification\All;

/**
 * Interceptor class for @see \PagBank\PaymentMagento\Controller\Notification\All
 */
class Interceptor extends \PagBank\PaymentMagento\Controller\Notification\All implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\PagBank\PaymentMagento\Gateway\Config\Config $config, \Magento\Framework\App\Action\Context $context, \Magento\Framework\Serialize\Serializer\Json $json, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria, \Magento\Sales\Api\TransactionRepositoryInterface $transaction, \Magento\Sales\Model\OrderRepository $orderRepository, \Magento\Framework\View\Result\PageFactory $pageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Payment\Model\Method\Logger $logger, \Magento\Framework\Notification\NotifierInterface $notifierPool, \Magento\Sales\Model\Order\CreditmemoFactory $creditMemoFactory, \Magento\Sales\Model\Service\CreditmemoService $creditMemoService, \Magento\Sales\Model\Order\Invoice $invoice)
    {
        $this->___init();
        parent::__construct($config, $context, $json, $searchCriteria, $transaction, $orderRepository, $pageFactory, $resultJsonFactory, $logger, $notifierPool, $creditMemoFactory, $creditMemoService, $invoice);
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
