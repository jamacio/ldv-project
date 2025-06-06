<?php
namespace Magento\Customer\Ui\Component\Listing\AttributeRepository;

/**
 * Interceptor class for @see \Magento\Customer\Ui\Component\Listing\AttributeRepository
 */
class Interceptor extends \Magento\Customer\Ui\Component\Listing\AttributeRepository implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Customer\Api\CustomerMetadataManagementInterface $customerMetadataManagement, \Magento\Customer\Api\AddressMetadataManagementInterface $addressMetadataManagement, \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata, \Magento\Customer\Api\AddressMetadataInterface $addressMetadata, \Magento\Customer\Model\Indexer\Attribute\Filter $attributeFiltering, ?\Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider = null)
    {
        $this->___init();
        parent::__construct($customerMetadataManagement, $addressMetadataManagement, $customerMetadata, $addressMetadata, $attributeFiltering, $attributeMetadataDataProvider);
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getList');
        return $pluginInfo ? $this->___callPlugins('getList', func_get_args(), $pluginInfo) : parent::getList();
    }
}
