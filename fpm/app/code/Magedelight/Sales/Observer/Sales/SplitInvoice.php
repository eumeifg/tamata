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

use \Magento\Framework\Event\ObserverInterface;
use \Magedelight\Sales\Model\Sales\Service\InvoiceService;
use \Magedelight\Sales\Model\Order as VendorOrder;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magedelight\Commissions\Model\Commission\Payment as CommissionPayment;

class SplitInvoice extends \Magedelight\Sales\Observer\Sales\AbstractSplitInvoice implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

        if ($paymentMethod == 'paypal_express' && $this->_vendorOrder->canSplitInvoice($order)) {
            $mainOrderInvoice = $payment->getOrder()->prepareInvoice();
            /* Capture Amount */
            $paymentAction = $this->_appConfigScopeConfigInterface
                ->getValue('payment/paypal_express/payment_action');
            if ($paymentAction == "Sale") {
                $payment->capture($mainOrderInvoice);
                $payment->setCreatedInvoice($mainOrderInvoice);
                $transactionId = $payment->getLastTransId();
                $paymentAdditionalInfo = $payment->getAdditionalInformation();
                if (!$payment->getIsTransactionPending() && $paymentAdditionalInfo['paypal_payment_status'] == \Magento\Paypal\Model\Info::PAYMENTSTATUS_COMPLETED) {
                    $payment = $this->updatePaymentCaptureData($payment);
                }
                if ($transactionId) {
                    $this->createInvoiceForVendors($order, $payment, $transactionId);
                }
            } else {
                /*
                 * Code to add Transaction Details
                 * Skip for now. Transaction details for PayPal Express will generated using cron.
                 */
                /* $order = $this->_processInvoice($order, $payment);*/

                $this->_confirmVendorOrders($order->getId());
                $order->setIsConfirmed(1)->save();
            }
        }
    }
}
