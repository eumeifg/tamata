<?php

namespace CAT\Custom\Model\Entity;

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use CAT\Custom\Helper\Automation;
use CAT\Custom\Model\Source\Option;
use Magento\Framework\App\Filesystem\DirectoryList;
use CAT\Custom\Model\ResourceModel\Automation\CollectionFactory;
use Magedelight\Commissions\Model\ResourceModel\Commission\Payment\CollectionFactory as PaymentCollection;
use Magedelight\Commissions\Model\Commission\Payment as PaymentCommissionModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class VendorPayment 
{
    const STATUS = 1;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var Automation
     */
    protected $automationHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PaymentCollection
     */
    protected $paymentCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var array
     */
    protected $reportData = [['id', 'comments']];

    /**
     * @param Csv $csv
     * @param Filesystem $filesystem
     * @param Automation $automationHelper
     * @param CollectionFactory $collectionFactory
     * @param PaymentCollection $paymentCollectionFactory
     * @param ManagerInterface $eventManager
     * @param DateTime $date
     */
    public function __construct(
        Csv $csv,
        Filesystem $filesystem,
        Automation $automationHelper,
        CollectionFactory $collectionFactory,
        PaymentCollection $paymentCollectionFactory,
        ManagerInterface $eventManager,
        DateTime $date
        
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->_filesystem = $filesystem;
        $this->automationHelper = $automationHelper;
        $this->csv = $csv;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
        $this->_eventManager = $eventManager;
        $this->_date = $date;
    }

    public function updateVendorPaymentToPaid($logger) {
        $collection = $this->automationHelper->checkIfSheetAvailable(Option::VENDOR_PAYMENT_STATUS);
        if($collection) {
            $filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('/cat/'.Option::VENDOR_PAYMENT_STATUS.'/').$collection->getFileName();
            $csvData = $this->csv->getData($filePath);
            
            $allIds = [];
            foreach ($csvData as $row => $data) {
                if ($row === 0) {
                    $subOrderPosition = array_search('Orders Suborder Id', $data);
                    $paidPosition = array_search('Paid', $data);
                }

                if ($row >= 1 && $data[$paidPosition] == 0) {
                    try {
                        $paymentCollection = $this->paymentCollectionFactory->create();
                        $subOrderIdArray = explode('-',$data[$subOrderPosition]);
                        $vendorOrderId = end($subOrderIdArray);
                        $paymentCollection->addFieldToFilter('vendor_order_id', $vendorOrderId);
                        if($paymentCollection->getSize() > 0) {
                            $payment = $paymentCollection->getFirstItem();
                            if ($payment->getTransactionType() === PaymentCommissionModel::DEBIT_TRANSACTION_TYPE) {
                                $this->addToReport($data[$subOrderPosition], 'Sorry, Kindly select the Credit Note only to proceed the transaction.');
                            } else {
                                $allIds[] = $payment->getVendorPaymentId();
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
                                $this->addToReport($data[$subOrderPosition], 'successfully updated.');
                                $logger->info(__('Successfully Updated for ID # %1', $data[$subOrderPosition]));
                            }
                        }
                    } catch (\Exception $e) {
                        $this->addToReport($data[$subOrderPosition], $e->getMessage());
                        $logger->info(__('Error message for ID # %1, Message: %2',[$data[$subOrderPosition], $e->getMessage()]));
                    }
                } else {
                    $this->addToReport($data[$subOrderPosition], __('Already paid.'));
                    $logger->info(__('Already Paid for ID #%1', $data[$subOrderPosition]));
                }
            }
            if(count($allIds) > 0) {
                $eventParams = ['vendor_payment_ids' => $collection->getAllIds()];
                if ($debit_transaction) {
                    $eventParams = array_merge($eventParams, $debit_transaction);
                }

                // Add the debit transaction params for adjucement END --------
                $this->_eventManager->dispatch('vendor_payment_status_change', $eventParams);
            }
            $reportUrl = '';
            if(!empty($this->reportData)) {
                $fileName = Option::VENDOR_PAYMENT_STATUS.'_'.$collection->getImportId().'_'.time().'.csv';
                $reportUrl = $this->automationHelper->generateCsvFile($this->reportData, $fileName, Option::VENDOR_PAYMENT_STATUS);
            }
            $processedTime = $this->_date->gmtDate('Y-m-d H:i:s');
            $this->automationHelper->updateStatus($collection, $reportUrl, $processedTime);
            
        } else {
            $this->addToReport('', __('No records found!'));
            $logger->info(__('No records found!'));
        }
    }

    public function addToReport($id, $msg = 'success')
    {
        $this->reportData[] = [
            'id' => $id,
            'comment' => $msg
        ];
    }
}