<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Observer\Sales;

use Magedelight\Commissions\Model\Commission\Payment as CommissionPayment;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Sales\Model\Sales\Service\InvoiceService;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @author Rocket Bazaar Core Team
 */
class AbstractSplitInvoice
{
    /**
     * @var \Magedelight\Sales\Model\Order
     */
    protected $_vendorOrder;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $dbTransaction;

    /**
     * @var \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $vendorOrderCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @var \Magedelight\Commissions\Model\Commission\PaymentFactory
     */
    protected $commissionPaymentFactory;

    /**
     * @var \Magedelight\Commissions\Model\Commission\InvoiceFactory
     */
    protected $commInvoiceFactory;

    protected $_dbTransaction;

    /**
     * @param VendorOrder $vendorOrder
     * @param InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $dbTransaction
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory
     * @param \Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory
     * @param \Magedelight\Commissions\Model\Commission\InvoiceFactory $commInvoiceFactory
     */
    public function __construct(
        VendorOrder $vendorOrder,
        InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $dbTransaction,
        ScopeConfigInterface $appConfigScopeConfigInterface,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magedelight\Commissions\Model\Commission\PaymentFactory $commissionPaymentFactory,
        \Magedelight\Commissions\Model\Commission\InvoiceFactory $commInvoiceFactory
    ) {
        $this->_vendorOrder = $vendorOrder;
        $this->invoiceService = $invoiceService;
        $this->_dbTransaction = $dbTransaction;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->commissionPaymentFactory = $commissionPaymentFactory;
        $this->commInvoiceFactory = $commInvoiceFactory;
    }

    public function createInvoiceForVendors($order, $payment, $transactionId)
    {
        $orderId = $order->getId();
        $vendors = $invoiceItems = [];

        foreach ($order->getAllItems() as $item) {
            $vendorId = $item->getData('vendor_id');
            if (!in_array($vendorId, $vendors)) {
                $vendors[] = $vendorId;
            }
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getData('vendor_id') != $vendorId) {
                $invoiceItems[$item->getQuoteItemId()] = 0;
            } else {
                $invoiceItems[$item->getQuoteItemId()] = $item->getQtyOrdered();
            }
        }
        $vendors = array_unique(array_filter($vendors));

        foreach ($vendors as $vendorId) {
            $vendorOrder = $this->_vendorOrder->getByOriginOrderId($orderId, $vendorId);

            if ($vendorOrder) {
                $vendorOrder->setData('is_confirmed', 1);
                $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems, $vendorId);

                $invoice->setVendorOrder($vendorOrder);
                $invoice->setOrder($order);

                $invoice->setSubtotal($vendorOrder->getSubtotal());
                $invoice->setBaseSubtotal($vendorOrder->getBaseSubtotal());
                $invoice->setGrandTotal($vendorOrder->getGrandTotal());
                $invoice->setBaseGrandTotal($vendorOrder->getBaseGrandTotal());

                $invoice->setTaxAmount($vendorOrder->getTaxAmount());
                $invoice->setBaseTaxAmount($vendorOrder->getBaseTaxAmount());
                $invoice->setShippingAmount($vendorOrder->getShippingAmount());
                $invoice->setShippingTaxAmount($vendorOrder->getBaseShippingTaxAmount());
                $invoice->setBaseShippingAmount($vendorOrder->getBaseShippingAmount());
                $invoice->setBaseShippingTaxAmount($vendorOrder->getBaseShippingTaxAmount());
                $invoice->setShippingInclTax($vendorOrder->getShippingInclTax());
                $invoice->setBaseShippingInclTax($vendorOrder->getBaseShippingInclTax());

                $invoice->setDiscountAmount($vendorOrder->getDiscountAmount());
                $invoice->setBaseDiscountAmount($vendorOrder->getBaseDiscountAmount());
                $invoice->setBaseDiscountTaxCompensationAmount($vendorOrder->getBaseDiscountTaxCompensationAmount());
                $invoice->setDiscountTaxCompensationAmount($vendorOrder->getDiscountTaxCompensationAmount());
                $invoice->setShippingDiscountTaxCompensationAmount(
                    $vendorOrder->getShippingDiscountTaxCompensationAmount()
                );
                $invoice->setBaseShippingDiscountTaxCompensationAmnt(
                    $vendorOrder->getBaseShippingDiscountTaxCompensationAmnt()
                );

                $invoice->setTransactionId($transactionId);
                $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);

                if (!$invoice) {
                    throw new LocalizedException(__('We can\'t save the invoice right now.'));
                }

                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $invoice->getOrder()->setIsInProcess(true);
                $order->addRelatedObject($invoice);
                $transactionSave = $this->_dbTransaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $vendorOrder->registerInvoice($invoice);
                $vendorOrder->setData('main_order', $invoice->getOrder());
                $transactionSave->addObject($vendorOrder);

                $transactionSave->save();
                $order->addRelatedObject($vendorOrder);
            }
        }
        $order = $this->_processInvoice($order, $payment);
        $order->setData('is_confirmed', 1)->setData('__dummy', 1)->save();
        //$order->setIsConfirmed(1)->setTotalPaid($payment->getAmountPaid())->setBaseTotalPaid($payment->getBaseAmountPaid())->save();
    }

    public function updatePaymentCaptureData($payment)
    {
        $data = [];
        $payment->setBaseShippingCaptured($payment->getBaseShippingAmount());
        $payment->setShippingCaptured($payment->getShippingAmount());
        $payment->setBaseAmountPaid($payment->getBaseAmountOrdered());
        $payment->setAmountPaid($payment->getAmountOrdered());
        return $payment;
    }

    protected function _processInvoice($order, $payment)
    {
        $vendorOrders = $this->vendorOrderCollectionFactory->create()
            ->addFieldToFilter("order_id", $order->getId());
        foreach ($vendorOrders as $vendorOrder) {
            if ($vendorOrder->getSubtotalInvoiced() == $vendorOrder->getSubtotal()) {
                $vendorOrder->setStatus(VendorOrder::STATUS_PACKED)->setIsConfirmed(1)->save();
            }
        }
        /*
         * Code to add Transaction Details
         * Skip for now. Transaction details for PayPal Express will generated using cron.
         */
        /* return $order = $this->_addTransactionDetails($order, $payment, $vendorOrders);*/

        return $order;
    }

    protected function _confirmVendorOrders($orderId)
    {
        $vendorOrders = $this->vendorOrderCollectionFactory->create()
            ->addFieldToFilter("order_id", $orderId);
        foreach ($vendorOrders as $vendorOrder) {
            $vendorOrder->setIsConfirmed(1)->save();
        }
    }

    protected function _addTransactionDetails($order, $payment, $vendorOrders)
    {
        $commissionPOModel = $this->commissionPaymentFactory->create();
        /* Generate PO */
        $this->_generatePO($order, $commissionPOModel, $vendorOrders);

        $payouts = $commissionPOModel->getCollection()
            ->addFieldToFilter('vendor_order_id', ['in' => $vendorOrders->getAllIds()]);

        if (count($payouts) > 0) {
            $transactions = [];
            foreach ($payouts as $pt) {
                $vId = $pt->getVendorId();

                $transactions[$vId] = [
                    "total_commission" => $pt->getTotalCommission(),
                    "marketplace_fee" => $pt->getMarketplaceFee(),
                    "cancellation_fee" => $pt->getCancellationFee(),
                    "transaction_fee" => $pt->getTransactionFee(),
                    "service_tax" => $pt->getServiceTax(),
                    "adjustment_amount" => $pt->getAdjustmentAmount(),
                    "amount" => $pt->getTotalAmount(),
                    "vendor_id" => $vId
                ];
                $commInvoice = $this->commInvoiceFactory->create();
                $commInvoice->setData($transactions[$vId]);
                $commInvoice->save();
                $pt->afterPay();
                $order->addRelatedObject($pt);
            }
        }
        return $order;
    }

    protected function _generatePO($order, $commissionPOModel, $vendorOrders)
    {
        $pos = [];
        foreach ($vendorOrders as $vendorOrder) {
            $pos[] = $commissionPOModel->generatePO($vendorOrder);
        }
        if (!empty($pos)) {
            $connection = $order->getResource()->getConnection();
            $connection->insertMultiple($connection->getTableName(CommissionPayment::VENDOR_PAYMENTS_TABLE), $pos);
            if (!empty($vendorOrders->getAllIds())) {
                /*
                 * Mark vendor order as po_generated
                 */
                $connection->update(
                    $order->getResource()->getTable(CommissionPayment::VENDOR_ORDER_TABLE),
                    ['po_generated' => 1],
                    ["vendor_order_id IN(?)" => $vendorOrders->getAllIds()]
                );
            }
            unset($connection);
        }
    }
}
