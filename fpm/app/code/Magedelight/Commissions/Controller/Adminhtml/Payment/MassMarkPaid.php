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
namespace Magedelight\Commissions\Controller\Adminhtml\Payment;

use Magedelight\Commissions\Model\Commission\Payment as PaymentCommissionModel;

use Magento\Framework\Controller\ResultFactory;

class MassMarkPaid extends AbstractMassAction
{
    const STATUS = 1;

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Commissions::mark_pending');
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $collectionSize = $collection->getSize();
        if (!$collectionSize) {
            $this->messageManager->addNotice(__('Please select payments to proceed.'));
        }
        $debit_transaction = [];
        try {
            foreach ($collection as $payment) {
                if ($payment->getTransactionType() === PaymentCommissionModel::DEBIT_TRANSACTION_TYPE) {
                    $this->messageManager->addError(__("Sorry, Kindly select the
                     Credit Note only to proceed the transaction."));
                } else {

                     /**
                * @todo calculate total amount for commission , and other fees of marketplace we collect from vendor.
                * $invoiceAmount += ($payment->getTotalCommission() + $payment->getMarketplaceFee() +
                      * $payment->getCancellationFee() + $payment->getTransactionFee());
                */
                    $payment->setTotalPaid($payment->getTotalAmount())
                    ->setPaidAt($this->_date->gmtTimestamp())
                    ->setStatus(self::STATUS)
                    ->save();

                    // -------- Add the debit transaction params for adjucement START
                    // if($payment->getTransactionType() === PaymentCommissionModel::CREDIT_TRANSACTION_TYPE){

                    $debit_transaction['vndr_transction_ids_amounts'][] =  [
                               "p_payment_id" => $payment->getVendorPaymentId() ,
                               "vendor_id" => $payment->getVendorId(),
                               "amount"=> $payment->getTotalAmount(),
                               "purchase_order_id" => $payment->getPurchaseOrderId()];

                    // }
                }
            }
            $eventParams = ['vendor_payment_ids' => $collection->getAllIds()];
            if ($debit_transaction) {
                $eventParams = array_merge($eventParams, $debit_transaction);
            }

            // Add the debit transaction params for adjucement END --------
            $this->_eventManager->dispatch('vendor_payment_status_change', $eventParams);
            /**
             * @todo generate commission invoice
             */
            $this->messageManager->addSuccess(__('A total of %1 payment(s) have been marked
             as paid.', $collectionSize));
        } catch (\Exception $ex) {
            $this->messageManager->addError(__($ex->getMessage()));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
