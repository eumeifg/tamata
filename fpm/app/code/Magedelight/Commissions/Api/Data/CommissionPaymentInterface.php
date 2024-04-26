<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Api\Data;

/**
 * @api
 */
interface CommissionPaymentInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const PURCHASE_ORDER_ID = 'purchase_order_id';
    const VENDOR_ORDER_ID = 'vendor_order_id';
    const VENDOR_ID = 'vendor_id';
    const COMMENT = 'comment';
    const COMMISSION_LEVEL = 'commission_level';
    const TOTAL_COMMISSION = 'total_commission';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const TAX_AMOUNT = 'tax_amount';
    const ADJUSTMENT_AMOUNT = 'adjustment_amount';
    const TOTAL_AMOUNT = 'total_amount';
    const TOTAL_DUE = 'total_due';
    const TOTAL_PAID = 'total_paid';
    const MARKETPLACE_FEE = 'marketplace_fee';
    const TRANSACTION_FEE = 'transaction_fee';
    const CANCELLATION_FEE = 'cancellation_fee';
    const SERVICE_TAX = 'service_tax';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PAID_AT = 'paid_at';

    /**
     * Payment Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Payment Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);
    
    /**
     * Purchase order Id
     *
     * @return int|null
     */
    public function getPurchaseOrderId();

    /**
     * Set Purchase Order Id
     *
     * @param int $purchaseOrderId
     * @return $this
     */
    public function setPurchaseOrderId($purchaseOrderId);
    
    /**
     * Vendor Id
     *
     * @return int|null
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     *
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Commission calculation type
     *
     * @return string|null
     */
    public function getComment();

    /**
     * Set Commission calculation type
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Commission level
     *
     * @return float
     */
    public function getCommissionLevel();

    /**
     * Set Commission level
     *
     * @param float $commissionLevel
     * @return $this
     */
    public function setCommissionLevel($commissionLevel);
    
    /**
     * Total commission for vendor order
     *
     * @return float
     */
    public function getTotalCommission();

    /**
     * Set Total commission for vendor order
     *
     * @param float $totalCommission
     * @return $this
     */
    public function setTotalCommission($totalCommission);
    
    /**
     * Shipping Amount for vendor order
     *
     * @return float
     */
    public function getShippingAmount();

    /**
     * Set Shipping Amount for vendor order
     *
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount);
    
    /**
     * Tax Amount for vendor order
     *
     * @return float
     */
    public function getTaxAmount();

    /**
     * Set Tax Amount for vendor order
     *
     * @param float $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);
    
    /**
     * Adjustment Amount for vendor order for partial payment
     *
     * @return float
     */
    public function getAdjustmentAmount();

    /**
     * Set Adjustment Amount for vendor order
     *
     * @param float $adjustmentAmount
     * @return $this
     */
    public function setAdjustmentAmount($adjustmentAmount);
    
    /**
     * Total Amount for vendor order
     *
     * @return float
     */
    public function getTotalAmount();

    /**
     * Set Total Amount for vendor order
     *
     * @param float $totalAmount
     * @return $this
     */
    public function setTotalAmount($totalAmount);
    
    /**
     * Total Amount Due for vendor order
     *
     * @return float
     */
    public function getTotalDue();

    /**
     * Set Total Amount Due for vendor order
     *
     * @param float $totalDue
     * @return $this
     */
    public function setTotalDue($totalDue);
    
    /**
     * Total Amount Due for vendor order
     *
     * @return float
     */
    public function getTotalPaid();

    /**
     * Set Total Amount Due for vendor order
     *
     * @param float $totalPaid
     * @return $this
     */
    public function setTotalPaid($totalPaid);

    /**
     * Marketplace Fee for vendor order
     *
     * @return float
     */
    public function getMarketplaceFee();

    /**
     * Set Marketplace Fee for vendor order
     *
     * @param float $marketplaceFee
     * @return $this
     */
    public function setMarketplaceFee($marketplaceFee);
    
    /**
     * Transaction Fee for vendor order
     *
     * @return float
     */
    public function getTransactionFee();

    /**
     * Set Transaction Fee for vendor order
     *
     * @param float $transactionFee
     * @return $this
     */
    public function setTransactionFee($transactionFee);
    
    /**
     * Order Cancellation Fee in case order cancelled by vendor
     *
     * @return float
     */
    public function getCancellationFee();

    /**
     * Set Order Cancellation Fee in case order cancelled by vendor
     *
     * @param float $cancellationFee
     * @return $this
     */
    public function setCancellationFee($cancellationFee);

    /**
     * Service tax
     *
     * @return float
     */
    public function getServiceTax();

    /**
     * Set Service tax
     *
     * @param float $serviceTax
     * @return $this
     */
    public function setServiceTax($serviceTax);

    /**
     * status
     *
     * @return boolean
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param boolean $status
     * @return $this
     */
    public function setStatus($status);
    
    /**
     * payment created at
     *
     * @return timestamp
     */
    public function getCreatedAt();
    
    /**
     * payment updated at
     *
     * @return timestamp
     */
    public function getUpdatedAt();
    
    /**
     * payment paid at
     *
     * @return timestamp
     */
    public function getPaidAt();
    
    /**
     * payment paid at
     *
     * @param timestamp $paidAt
     * @return $this
     */
    public function setPaidAt($paidAt);
}
