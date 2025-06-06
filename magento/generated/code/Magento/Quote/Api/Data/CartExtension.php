<?php
namespace Magento\Quote\Api\Data;

/**
 * Extension class for @see \Magento\Quote\Api\Data\CartInterface
 */
class CartExtension extends \Magento\Framework\Api\AbstractSimpleObject implements CartExtensionInterface
{
    /**
     * @return \Magento\Quote\Api\Data\ShippingAssignmentInterface[]|null
     */
    public function getShippingAssignments()
    {
        return $this->_get('shipping_assignments');
    }

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface[] $shippingAssignments
     * @return $this
     */
    public function setShippingAssignments($shippingAssignments)
    {
        $this->setData('shipping_assignments', $shippingAssignments);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPagbankInterestAmount()
    {
        return $this->_get('pagbank_interest_amount');
    }

    /**
     * @param float $pagbankInterestAmount
     * @return $this
     */
    public function setPagbankInterestAmount($pagbankInterestAmount)
    {
        $this->setData('pagbank_interest_amount', $pagbankInterestAmount);
        return $this;
    }

    /**
     * @return float|null
     */
    public function getBasePagbankInterestAmount()
    {
        return $this->_get('base_pagbank_interest_amount');
    }

    /**
     * @param float $basePagbankInterestAmount
     * @return $this
     */
    public function setBasePagbankInterestAmount($basePagbankInterestAmount)
    {
        $this->setData('base_pagbank_interest_amount', $basePagbankInterestAmount);
        return $this;
    }
}
