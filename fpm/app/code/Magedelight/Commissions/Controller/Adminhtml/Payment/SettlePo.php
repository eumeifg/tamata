<?php

namespace Magedelight\Commissions\Controller\Adminhtml\Payment;

use Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory;

use Magento\Backend\App\Action\Context;

use Magento\Framework\Data\Form\FormKey;

class SettlePo extends \Magento\Backend\App\Action
{

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     *
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    protected $formKey;

    protected $_paymentcom;

    protected $_date;

    protected $historyFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        \Magedelight\Commissions\Model\Commission\Payment $payment,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Commissions\Model\Commission\HistoryFactory $historyFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->_paymentcom = $payment;
        $this->_date = $date;
        $this->historyFactory = $historyFactory;
        $this->request = $context->getRequest();
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            if ($this->getRequest()->isAjax()) {
                $credit_purchase_order_id = $this->request->getPost('credit_purchase_order_id');
                $credit_payment_id = $this->request->getPost('credit_payment_id');
                $vendor_id = $this->request->getPost('vendor_id');

                $debit_total_amount = 0;
                $debit_purchase_order_ids = [];

                /* Bank transaction form detail */
                $bank_transaction_id = $this->request->getPost('bank_transaction_id');
                $settled_transction_amount = $this->request->getPost('settled_transction_amount');
                $requsted_transaction_date = strtotime($this->request->getPost('bank_transaction_date'));
                $bank_transaction_date = date("Y-m-d H:i:s", $requsted_transaction_date);

                if ($bank_transaction_id && !empty($bank_transaction_id)) {
                    $paymentHistory = $this->historyFactory->create();

                    $paymentHistory->setVendorPaymentId($credit_payment_id);
                    $paymentHistory->setVendorId($vendor_id);
                    $paymentHistory->setTransactionId($bank_transaction_id);
                    $paymentHistory->setTransactionAmount($settled_transction_amount);
                    $paymentHistory->setTransactionDate($bank_transaction_date);
                    $paymentHistory->setCreatedAt($this->_date->gmtTimestamp());

                    $paymentHistory->save();
                }
                /* Bank transaction form detail */

                $credit_trans_collection = $this->_paymentcom->getCollection()
                                ->addFieldToFilter('vendor_payment_id', $credit_payment_id);

                if (null !== $this->request->getPost('selected_debit_ids')) {
                    $selected_debit_ids = implode(",", $this->request->getPost('selected_debit_ids'));

                    $debit_trans_collection = $this->_paymentcom->getCollection()
                                ->addFieldToFilter('vendor_payment_id', ['in' => $selected_debit_ids]);

                    foreach ($debit_trans_collection as $key => $debit_values) {
                        $debit_total_amount += $debit_values->getTotalAmount();
                        $debit_purchase_order_ids[] = $debit_values->getPurchaseOrderId();

                        $debit_values->setIsSettled(1);
                        $debit_values->setSettledTransactionId($credit_purchase_order_id);
                        $debit_values->setStatus(1);
                        $debit_values->setPaidAt($this->_date->gmtTimestamp());

                        $debit_values->save();
                    }
                }

                foreach ($credit_trans_collection as $key => $credit_values) {
                    $updated_cedit_total_amount = $credit_values->getTotalAmount() + $debit_total_amount;

                    $credit_values->setTotalAmount($updated_cedit_total_amount);
                    $credit_values->setTotalPaid($updated_cedit_total_amount);
                    $credit_values->setStatus(1);
                    $credit_values->setPaidAt($this->_date->gmtTimestamp());

                    if (null !== $this->request->getPost('selected_debit_ids')) {
                        $credit_values->setIsSettled(1);
                        $credit_values->setSettledTransactionId(implode(",", $debit_purchase_order_ids));
                    }

                    $credit_values->save();
                }

                $this->messageManager->addSuccess(__('Payment has been marked as paid.'));

                //return $resultRedirect->setPath('commissionsadmin/payment/index');
            }
        } catch (\Exception $ex) {

            //$this->messageManager->addError(__($ex->getMessage()));

            $this->messageManager->addError(__("Something went wrong please try again !!!"));

            //return $resultRedirect->setPath('commissionsadmin/payment/index');
        }
    }
}
