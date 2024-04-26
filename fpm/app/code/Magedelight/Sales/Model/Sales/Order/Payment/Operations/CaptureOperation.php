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
namespace Magedelight\Sales\Model\Sales\Order\Payment\Operations;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Model\Order\Payment\State\CommandInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;

class CaptureOperation extends \Magento\Sales\Model\Order\Payment\Operations\CaptureOperation
{
    public function __construct(
        CommandInterface $stateCommand,
        BuilderInterface $transactionBuilder,
        ManagerInterface $transactionManager,
        EventManagerInterface $eventManager,
        \Magedelight\Sales\Model\Order $vendorOrder
    ) {
        $this->stateCommand = $stateCommand;
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionManager = $transactionManager;
        $this->eventManager = $eventManager;
        $this->vendorOrder = $vendorOrder;
        parent::__construct($stateCommand, $transactionBuilder, $transactionManager, $eventManager);
    }
    /**
     * Captures payment.
     *
     * @param OrderPaymentInterface $payment
     * @param InvoiceInterface|null $invoice
     * @return OrderPaymentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(OrderPaymentInterface $payment, $invoice)
    {
        /**
         * @var $payment Payment
         */
        if (null === $invoice) {
            /* Stop default Invoice if Payment is PayPal express */
            if ($payment->getMethod() != 'paypal_express' ||
                !$this->vendorOrder->canSplitInvoice($payment->getOrder())) {
                $invoice = $this->invoice($payment);
                $payment->setCreatedInvoice($invoice);
            }

            if ($payment->getIsFraudDetected()) {
                $payment->getOrder()->setStatus(Order::STATUS_FRAUD);
            }
            return $payment;
        }
        $amountToCapture = $payment->formatAmount($invoice->getBaseGrandTotal());
        $order = $payment->getOrder();

        $payment->setTransactionId(
            $this->transactionManager->generateTransactionId(
                $payment,
                Transaction::TYPE_CAPTURE,
                $payment->getAuthorizationTransaction()
            )
        );

        $this->eventManager->dispatch(
            'sales_order_payment_capture',
            ['payment' => $payment, 'invoice' => $invoice]
        );

        /**
         * Fetch an update about existing transaction. It can determine whether the transaction can be paid
         * Capture attempt will happen only when invoice is not yet paid and the transaction can be paid
         */
        if ($invoice->getTransactionId()) {
            $method = $payment->getMethodInstance();
            $method->setStore(
                $order->getStoreId()
            );
            if ($method->canFetchTransactionInfo()) {
                $method->fetchTransactionInfo(
                    $payment,
                    $invoice->getTransactionId()
                );
            }
        }

        if ($invoice->getIsPaid()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The transaction "%1" cannot be captured yet.', $invoice->getTransactionId())
            );
        }

        // attempt to capture: this can trigger "is_transaction_pending"
        $method = $payment->getMethodInstance();
        $method->setStore(
            $order->getStoreId()
        );
        //TODO replace for sale usage
        $method->capture($payment, $amountToCapture);

        // prepare parent transaction and its amount
        $paidWorkaround = 0;
        if (!$invoice->wasPayCalled()) {
            $paidWorkaround = (double)$amountToCapture;
        }
        if ($payment->isCaptureFinal($paidWorkaround)) {
            $payment->setShouldCloseParentTransaction(true);
        }

        $transactionBuilder = $this->transactionBuilder->setPayment($payment);
        $transactionBuilder->setOrder($order);
        $transactionBuilder->setFailSafe(true);
        $transactionBuilder->setTransactionId($payment->getTransactionId());
        $transactionBuilder->setAdditionalInformation($payment->getTransactionAdditionalInfo());
        $transactionBuilder->setSalesDocument($invoice);
        $transaction = $transactionBuilder->build(Transaction::TYPE_CAPTURE);

        $message = $this->stateCommand->execute($payment, $amountToCapture, $order);
        if ($payment->getIsTransactionPending()) {
            $invoice->setIsPaid(false);
        } else {
            $invoice->setIsPaid(true);
            $this->updateTotals($payment, ['base_amount_paid_online' => $amountToCapture]);
        }
        $message = $payment->prependMessage($message);
        $payment->addTransactionCommentsToOrder($transaction, $message);
        $invoice->setTransactionId($payment->getLastTransId());

        return $payment;
    }
}
