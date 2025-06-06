<?php
namespace Magento\Quote\Api\Data;

/**
 * ExtensionInterface class for @see \Magento\Quote\Api\Data\CartInterface
 */
interface CartExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    /**
     * @return \Magento\Quote\Api\Data\ShippingAssignmentInterface[]|null
     */
    public function getShippingAssignments();

    /**
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface[] $shippingAssignments
     * @return $this
     */
    public function setShippingAssignments($shippingAssignments);

    /**
     * @return float|null
     */
    public function getPagbankInterestAmount();

    /**
     * @param float $pagbankInterestAmount
     * @return $this
     */
    public function setPagbankInterestAmount($pagbankInterestAmount);

    /**
     * @return float|null
     */
    public function getBasePagbankInterestAmount();

    /**
     * @param float $basePagbankInterestAmount
     * @return $this
     */
    public function setBasePagbankInterestAmount($basePagbankInterestAmount);
}
