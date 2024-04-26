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
namespace Magedelight\Commissions\Model;

use Magento\Framework\Model\AbstractModel;
use Magedelight\Commissions\Api\Data\CommissionInvoiceInterface;

class CommissionInvoice extends \Magento\Framework\DataObject implements CommissionInvoiceInterface
{
    /**
     * @inheritdoc
     */
    public function getVendorPaymentId()
    {
        return $this->getData(CommissionInvoiceInterface::VENDOR_PAYMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVendorPaymentId($paymentId)
    {
        return $this->setData(CommissionInvoiceInterface::VENDOR_PAYMENT_ID, $paymentId);
    }
        
    /**
     * @inheritdoc
     */
    public function getVendorOrderId()
    {
        return $this->getData(CommissionInvoiceInterface::VENDOR_ORDER_ID);
    }
    
    /**
     * @inheritdoc
     */
    public function setVendorOrderId($vendorOrderId)
    {
        return $this->setData(CommissionInvoiceInterface::VENDOR_ORDER_ID, $vendorOrderId);
    }
    
    /**
     * @inheritdoc
     */
    public function getPurchaseOrderId()
    {
        return $this->getData(CommissionInvoiceInterface::PURCHASE_ORDER_ID);
    }
    
    /**
     * @inheritdoc
     */
    public function setPurchaseOrderId($purchaseOrderId)
    {
        return $this->setData(CommissionInvoiceInterface::PURCHASE_ORDER_ID, $purchaseOrderId);
    }
    
    /**
     * @inheritdoc
     */
    public function getPaidAt()
    {
        return $this->getData(CommissionInvoiceInterface::PAID_AT);
    }
    
    /**
     * @inheritdoc
     */
    public function setPaidAt($paidAt)
    {
        return $this->setData(CommissionInvoiceInterface::PAID_AT, $paidAt);
    }
    
    /**
     * @inheritdoc
     */
    public function getCommissionInvoiceId()
    {
        return $this->getData(CommissionInvoiceInterface::COMMISSION_INV_ID);
    }
    
    /**
     * @inheritdoc
     */
    public function setCommissionInvoiceId($invoiceId)
    {
        return $this->setData(CommissionInvoiceInterface::COMMISSION_INV_ID, $invoiceId);
    }
    
    /**
     * @inheritdoc
     */
    public function getTotalFees()
    {
        return $this->getData(CommissionInvoiceInterface::TOTAL_FEES);
    }
    
    /**
     * @inheritdoc
     */
    public function setTotalFees($totalFees)
    {
        return $this->setData(CommissionInvoiceInterface::TOTAL_FEES, $totalFees);
    }
}
