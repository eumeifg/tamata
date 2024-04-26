<?php
/**
 * A Magento 2 module that functions for Warehouse management
 * Copyright (C) 2019
 *
 * This file included in Ktpl/Warehousemanagement is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Ktpl\Warehousemanagement\Controller\Adminhtml\Warehousemanagement;

use Magento\Framework\Exception\LocalizedException;
use Ktpl\Warehousemanagement\Model\WarehousemanagementFactory;
use Ktpl\Tookan\Model\Config\Source\TookanStatus;
use Magedelight\Sales\Model\Order as VendorOrder;
use MDC\Sales\Model\Source\Order\PickupStatus;

class SaveEvent extends \Magento\Backend\App\Action
{
	
	/**
     * Logging instance
     * @var \Ktpl\Warehousemanagement\Logger\Logger
     */
    protected $_logger;
    
    protected $dataPersistor;

    protected $vendorOrderCollectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        WarehousemanagementFactory $warehousemanagement,
        \Magedelight\Sales\Model\ResourceModel\Order\CollectionFactory $vendorOrderCollectionFactory,
        \Ktpl\Warehousemanagement\Helper\Data $warehouseHelper,
        \Ktpl\BarcodeGenerator\Model\Barcode $barcode,
        \Ktpl\Warehousemanagement\Logger\Logger $logger,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->warehousemanagement = $warehousemanagement;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
        $this->warehouseHelper = $warehouseHelper;
        $this->barcode = $barcode;
        $this->_logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $resultJson = $this->resultJsonFactory->create();
        $error = [];

        if ($data) {
            unset($data['form_key']);
			$this->_logger->info('Product Delivery : From Warehouse to Customer - Starts');
            foreach ($data as $singleRaw) {
                $model = $this->warehousemanagement->create();
                $warehouseCollection = $model->getCollection()
                                        ->addFieldToFilter('product_location',$singleRaw['product_location'])
                                        ->addFieldToFilter('barcode_number',$singleRaw['barcode_number'])
                                        ->addFieldToFilter('product_id',$singleRaw['product_id'])
                                        ->getData();
                                        
                                        //print_r($warehouseCollection);die;

                /*...To check for duplicate barcode scan...*/
                if (empty($warehouseCollection)) {
                    $model->setData($singleRaw);
                    $model->save();
                    $this->_logger->info('save - product id - '.$singleRaw['product_id']);
                } else {
                    $error['duplicateInDb'] = 0;
                    $resultJson->setData($error);
                    $this->_logger->info('duplicate error - '.$singleRaw['product_id']);
                    return $resultJson;
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
                        $this->_eventManager->dispatch('vendor_orders_in_transit_after', ['vendor_order' => $vendorOrder]);
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
                        $this->_eventManager->dispatch('vendor_orders_out_warehouse_after', ['vendor_order' => $vendorOrder]);
                        $this->_logger->info('save sub order for location == 1 - '.$singleRaw['product_id'] .'suborder id --'.$subOrderData[1]);
                    }
				}
                
                /*...To save Vendor order status...*/
                /*$vendorOrders = $this->vendorOrderCollectionFactory->create()
                    ->addFieldToFilter("increment_id", $singleRaw['sub_order_id']);
                if ($singleRaw['product_location'] == 0) {
                    $vendorOrderStatus = $this->warehouseHelper->getDeliveryVendortoWarehouseStatus();
                } else {
                    $vendorOrderStatus = $this->warehouseHelper->getDeliveryWarehousetoCustomer();
                }
                foreach ($vendorOrders as $vendorOrder) {
                    $vendorOrder->setData('status', $vendorOrderStatus);
                    $vendorOrder->save();
                }*/

                $msgTempFlag = $singleRaw['main_order_status'];
            }
            $this->_logger->info('Product Delivery : From Warehouse to Customer - Ends');
            if ($msgTempFlag == 0) {
                $this->messageManager->addSuccessMessage(__('You saved vendor to warehouse record successfully.'));
            } else {
                $this->messageManager->addSuccessMessage(__('You saved warehouse to customer record successfully.'));
            }
            return $resultRedirect->setPath('*/*/');
        }
    }
}
