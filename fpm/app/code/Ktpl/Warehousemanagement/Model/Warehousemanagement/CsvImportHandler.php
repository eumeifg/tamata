<?php

namespace Ktpl\Warehousemanagement\Model\Warehousemanagement;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Ktpl\Warehousemanagement\Model\WarehousemanagementFactory;
use Magedelight\Sales\Model\Order as VendorOrder;
use MDC\Sales\Model\Source\Order\PickupStatus;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;


/**
 * Tax Rate CSV Import Handler
 *
 * @api
 * @since 100.0.2
 */
class CsvImportHandler
{
    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $_publicStores;

    /**
     * CSV Processor
     *
     * @var Csv
     */
    protected $csvProcessor;

    protected $headers = [];
    
    protected $request;
    
     /**
     * @var EventManagerInterface
     */
    protected $eventManager;
    
    /**
     * Logging instance
     * @var \Ktpl\Warehousemanagement\Logger\Logger
     */
    protected $_logger;
    
    protected $vendorOrderCollectionFactory;

    /**
     * CsvImportHandler constructor.
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
     * @param Csv $csvProcessor
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection,
        \Ktpl\BarcodeGenerator\Model\Barcode $barcode,
        WarehousemanagementFactory $warehousemanagement,
        \Magento\Framework\App\Request\Http $request,
        \Ktpl\Warehousemanagement\Logger\Logger $logger,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Backend\Model\Auth\Session $authSession,
         EventManagerInterface $eventManager,
        Csv $csvProcessor
    ) {
        // prevent admin store from loading
        $this->_publicStores = $storeCollection->setLoadDefault(false);
        $this->barcode = $barcode;
        $this->warehousemanagement = $warehousemanagement;
        $this->request = $request;
        $this->_logger = $logger;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->remoteAddress = $remoteAddress;
        $this->authSession = $authSession;
        $this->eventManager = $eventManager;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Import from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new LocalizedException(__('Invalid file upload attempt.'));
        }
        $productLocation =  $this->request->getParam('deliverytype');
        $orderEvent =  $this->request->getParam('returntype');
        $visitoreIp = $this->remoteAddress->getRemoteAddress();
        $userId = $this->authSession->getUser()->getId();
        $barcodeRawData = $this->csvProcessor->getData($file['tmp_name']);
        

        foreach ($barcodeRawData as $rowIndex => $dataRow) {
			
			if($rowIndex == 0){
				continue;
			}
			$barcodeNumber = str_replace("#","",$dataRow[0]); 
			if(isset($barcodeNumber)) {
				$lineItemData = $this->barcode->getItemDataFromBarcode($barcodeNumber);
				
				if(is_array($lineItemData)) {
					foreach($lineItemData as $key => $singleRaw) {
						$this->_logger->info('Barcode Number- '.$barcodeNumber);
						$singleRaw['barcode_number'] = $barcodeNumber;
						$singleRaw['product_location'] = $productLocation;
						$singleRaw['order_event'] = $orderEvent;
						$singleRaw['ip_address'] = $visitoreIp;
						$singleRaw['user_id'] = $userId;
						$model = $this->warehousemanagement->create();
						$warehouseCollection = $model->getCollection()
											->addFieldToFilter('product_location',$singleRaw['product_location'])
											->addFieldToFilter('barcode_number',$singleRaw['barcode_number'])
											->addFieldToFilter('product_id',$singleRaw['product_id'])
											->getData();
						/*...To check for duplicate barcode scan...*/
						if (empty($warehouseCollection)) {
							$model->setData($singleRaw);
							$model->save();
							$this->_logger->info('save - product id - '.$singleRaw['product_id']);
						} else {
							$error['duplicateInDb'] = 0;
							$this->_logger->info('duplicate error - '.$singleRaw['product_id']);
							continue;
						}

						if ($singleRaw['product_location'] == 0) {
							$this->barcode->updateTookanStatus($singleRaw['barcode_number']);
							$this->_logger->info('set status location - 0 - '.$singleRaw['product_id']);
						} else {
							$this->barcode->updateTookanStatus($singleRaw['barcode_number'],TookanStatus::OUT_FOR_DELIVERY);
							$this->_logger->info('set status location not equals to 0 - '.$singleRaw['product_id']);
						}

						if ($singleRaw['product_location'] == 0) {
							$subOrderData = explode('-', $singleRaw["sub_order_id"]);
							if(!empty($subOrderData[1])){
								$vendorOrder = $this->vendorOrderCollectionFactory->create()
								->addFieldToFilter("vendor_order_id", $subOrderData[1])->getFirstItem();
								$vendorOrder->setStatus(
									VendorOrder::STATUS_IN_TRANSIT
								)->setPickupStatus(
									PickupStatus::PICKED
								)->save();
								$this->eventManager->dispatch('vendor_orders_in_transit_after', ['vendor_order' => $vendorOrder]);
								$this->_logger->info('save sub order for location == 0 - '.$singleRaw['product_id'] .'suborder id --'.$subOrderData[1]);
							}
						}
						else if ($singleRaw['product_location'] == 1) {
							$subOrderData = explode('-', $singleRaw["sub_order_id"]);
							if(!empty($subOrderData[1])){
								$vendorOrder = $this->vendorOrderCollectionFactory->create()
								->addFieldToFilter("vendor_order_id", $subOrderData[1])->getFirstItem();
								$vendorOrder->setStatus(
									VendorOrder::STATUS_OUT_WAREHOUSE
								)->setPickupStatus(
									PickupStatus::PICKED
								)->save();
								$this->eventManager->dispatch('vendor_orders_out_warehouse_after', ['vendor_order' => $vendorOrder]);
								$this->_logger->info('save sub order for location == 1 - '.$singleRaw['product_id'] .'suborder id --'.$subOrderData[1]);
							}
						}
						$msgTempFlag = $singleRaw['main_order_status'];
					}
				}
				else {
					$this->_logger->info('Barcode Data Not Found - '.$barcodeNumber);
				}
			}
        }
        
    }
}
