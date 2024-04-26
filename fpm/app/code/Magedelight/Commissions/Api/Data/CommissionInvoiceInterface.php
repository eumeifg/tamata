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
interface CommissionInvoiceInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const VENDOR_PAYMENT_ID ='vendor_payment_id';
    const VENDOR_ORDER_ID ='vendor_order_id';
    const PURCHASE_ORDER_ID ='purchase_order_id';
    const PAID_AT ='paid_at';
    const COMMISSION_INV_ID='commission_invoice_id';
    const TOTAL_FEES ='total_fees';

    /**
     * Get Vendor Payment Id
     *
     * @return int|null
     */
    public function getVendorPaymentId();

    /**
     * Set Vendor Payment Id
     * @param int|null $paymentId
     * @return $this
     */
    public function setVendorPaymentId($paymentId);

    /**
     * Get Vendor Order id
     *
     * @return int|null
     */
    public function getVendorOrderId();

    /**
     * Commission id
     * @param int|null $vendorOrderId
     * @return $this
     */
    public function setVendorOrderId($vendorOrderId);

    /**
     * Get Purchase Order Id
     *
     * @return string|null
     */
    public function getPurchaseOrderId();

    /**
     * Set Purchase Order Id
     * @param string|null $purchaseOrderId
     * @return $this
     */
    public function setPurchaseOrderId($purchaseOrderId);

    /**
     * Get Paid Date
     *
     * @return string|null
     */
    public function getPaidAt();

    /**
     * Set Paid Date
     * @param string|null $paidAt
     * @return $this
     */
    public function setPaidAt($paidAt);

    /**
     * Get Commission Invoice id
     *
     * @return string|null
     */
    public function getCommissionInvoiceId();

    /**
     * Set Commission Invoice id
     * @param string|null $invoiceId
     * @return $this
     */
    public function setCommissionInvoiceId($invoiceId);

    /**
     * Get Total Fees
     *
     * @return string|null
     */
    public function getTotalFees();

    /**
     * Set Total Fees
     * @param string|null $totalFees
     * @return $this
     */
    public function setTotalFees($totalFees);
}
