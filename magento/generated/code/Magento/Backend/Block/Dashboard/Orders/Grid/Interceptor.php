<?php
namespace Magento\Backend\Block\Dashboard\Orders\Grid;

/**
 * Interceptor class for @see \Magento\Backend\Block\Dashboard\Orders\Grid
 */
class Interceptor extends \Magento\Backend\Block\Dashboard\Orders\Grid implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Magento\Framework\Module\Manager $moduleManager, \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $backendHelper, $moduleManager, $collectionFactory, $data);
    }
}
