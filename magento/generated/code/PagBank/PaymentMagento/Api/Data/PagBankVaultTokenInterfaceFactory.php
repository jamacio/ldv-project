<?php
namespace PagBank\PaymentMagento\Api\Data;

/**
 * Factory class for @see \PagBank\PaymentMagento\Api\Data\PagBankVaultTokenInterface
 */
class PagBankVaultTokenInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\PagBank\\PaymentMagento\\Api\\Data\\PagBankVaultTokenInterface')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \PagBank\PaymentMagento\Api\Data\PagBankVaultTokenInterface
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
