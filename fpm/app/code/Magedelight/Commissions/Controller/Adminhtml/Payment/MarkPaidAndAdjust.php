<?php

namespace Magedelight\Commissions\Controller\Adminhtml\Payment;

use Magedelight\Commissions\Model\Commission\Payment as PaymentCommissionModel;
use Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory;

use Magento\Backend\App\Action\Context;

use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\Data\Form\FormKey;

class MarkPaidAndAdjust extends \Magento\Backend\App\Action
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

    protected $_storeManager;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');

        $currency = $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();

        /* 1. Get current id payment record */
        /* 2. Get current id's vendor id matched other debit payment records */
        $html = '';
        try {
            if ($this->getRequest()->isAjax()) {
                $payment_id = $this->getRequest()->getParam('payment_id');
                $transaction_type = $this->getRequest()->getParam('transaction_type');

                $collection = $this->_collectionFactory->create();
                $collection->addFieldToFilter('vendor_payment_id', ['eq' =>$payment_id]);

                $html .= '<div class="payment_data_response">';
                $html .= '<form id="credit_settle_form" name="credit_settle_form" method="post"
                            action="#" enctype="multipart/form-data" autocomplete="off">';

                $html .= '<input name="form_key" type="hidden" value=' . $this->formKey->getFormKey() . '>';
                $html .= '<input name="credit_payment_id" type="hidden" value=' . $payment_id . '>';
                $html .= '<input name="credit_purchase_order_id" type="hidden"
                 value=' . $collection->getData()[0]['purchase_order_id'] . '>';
                $html .= '<input name="vendor_id" type="hidden" value=' . $collection->getData()[0]['vendor_id'] . '>';
                $html .= '<input name="total_payable" type="hidden"
                 value=' . $collection->getData()[0]['total_amount'] . '>';

                $html .= '<div class="payment_heading">';
                $html .= '<span><b>' . __("Purchase Order Id :") . '</b> '
                    . $collection->getData()[0]['purchase_order_id'] . ' </span> <br/>';
                $html .= '<span><b>' . __("Transaction Type :") . '</b> '
                    . str_replace("_", ' ', ucwords(
                        $collection->getData()[0]['transaction_type'],
                        "_"
                    )) . ' </span> <br/>';
                $html .= '<span><b>' . __("Net Payable :") . '</b>'
                    . $currency . '</span><span id="changed_payable">'
                    . number_format($collection->getData()[0]['total_amount'], 2) . ' </span><br/>';

                $html .= '</div> <br/>';

                if ($transaction_type === PaymentCommissionModel::CREDIT_TRANSACTION_TYPE) {

                    $vendor_id = $collection->getData()[0]['vendor_id'];

                    $debitCollection = $this->_collectionFactory->create()
                    ->addFieldToFilter('transaction_type', PaymentCommissionModel::DEBIT_TRANSACTION_TYPE)
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->addFieldToFilter('status', ['neq' => 1])
                    ->addFieldToFilter('is_settled', ['eq' => 0])
                    ->setOrder('updated_at', 'asc');

                    $html .= '<span class="admin_suggestion_for_process">
<i>* You may settled following dues of Seller (Debit Notes).</i></span> <br/><br/>';

                    $html .= '<div class="debit_trans_table_list">';
                    $html .= '<table class="data-grid data-grid-draggable" data-role="grid">';
                    $html .= '<thead>';
                    $html .= '<tr>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Select") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Debit Notes") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Commission") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Marketplace Fee") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Service Tax") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Cancellation Fee") . '</th>';
                    $html .= '<th class="data-grid-multicheck-cell">' . __("Net Payable") . '</th>';
                    $html .= '</tr>';
                    $html .= '</thead>';

                    if (count($debitCollection) > 0) {
                        foreach ($debitCollection as $key => $debitValues) {
                            $html .= '<tr>';
                            $html .= '<td class="col" align="center"> <input type="checkbox" name="selected_debit_ids[]"
 value=' . $debitValues->getVendorPaymentId() . '
  data-price=' . number_format($debitValues->getTotalAmount(), 2) . ' class="debit_check"> </td>';
                            $html .= '<td class="col">' . $debitValues->getPurchaseOrderId() . '</td>';
                            $html .= '<td class="col">' . $currency .
                                number_format($debitValues->getTotalCommission(), 2) . '</td>';
                            $html .= '<td class="col">' . $currency .
                                number_format($debitValues->getMarketplaceFee(), 2) . '</td>';
                            $html .= '<td class="col">' . $currency .
                                number_format($debitValues->getServiceTax(), 2) . '</td>';
                            $html .= '<td class="col">' . $currency .
                                number_format($debitValues->getCancellationFee(), 2) . '</td>';
                            $html .= '<td class="col">' . substr_replace(number_format(
                                $debitValues->getTotalAmount(),
                                2
                            ), $currency, 1, 0) . '</td>';
                        }
                    } else {
                        $html .= '<td colspan="7" class="col" align="center">No records found !</td>';
                    }

                    $html .= '</tbody>';
                    $html .= '</table>';

                    $html .= '<div class="admin__scope-old"><div class="entry-edit form-inline">';
                    $html .= '<fieldset class="fieldset admin__fieldset ">';
                    $html .= '<br/><br/> <div class="bank_transction_detail_form">';

                    $html .= '<legend class="admin__legend legend">
						                <span>' . __("Bank Transaction Detail") . '</span>
						            </legend>';

                    $html .= '<div class="admin__field field ">
			
								<label class="label admin__field-label" for="vendor_address1">
								<span>' . __("Bank Transaction Id:") . '</span></label>
								
								<div class="admin__field-control control">
								<input name="bank_transaction_id" id="bank_transaction_id" 
								class="input-text admin__control-text" type="text" > 
								</div>

								</div>';

                    $html .= '<div class="admin__field field ">
			
								<label class="label admin__field-label" for="vendor_address1">
								<span>' . __("Paid Amount:") . '</span></label>
								
								<div class="admin__field-control control">
								<input type="number" name="settled_transction_amount" id="settled_transction_amount"
								 class="input-text admin__control-text " step="any" > 
								</div>

								</div>';

                    $html .= '<div class="admin__field field ">
			
								<label class="label admin__field-label" for="vendor_address1">
								<span>' . __("Transaction Date:") . '</span></label>
								
								<div class="admin__field-control control">
								<input type="text" name="bank_transaction_date" id="bank_transaction_date"
								 class="input-text admin__control-text hasDatepicker"  > 
								</div>

								</div>';

                    $html .= '</fieldset>';
                    $html .= '</div> ';

                    $html .= '</div>';

                    $html .= '</div>';

                    $html .= '<style>
							legend.admin__legend.legend {
							    border-bottom: 1px solid #cac3b4;
							    margin: 0px 0 18px;
							    padding: 0px 0 9px !important;
							}
							label.label.admin__field-label{
								width: 15%;
								display: inline-block;
  								text-align: right;
							}
							.admin__field-control.control {
								display:inline-block;
							}
							</style>';
                } else {
                    $html .= '<div class="debit_trans_selects_warning">';
                    $html .= '<span style="color:red"><b>' . __("Sorry, Kindly select the Credit Note
                     only to proceed the transaction.") . '</b></span>';
                    $html .= '</div>';
                }
                $html .= '</form>';
                $html .= '</div>';
            }
        } catch (\Exception $ex) {
            $html .= '<span style="color:red"><b>' . __($ex->getMessage()) . '</b></span>';
        }
        $response->setContents(json_encode($html));
        return $response;
    }
}
