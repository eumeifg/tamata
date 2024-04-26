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

class SaveReturn extends \Magento\Backend\App\Action
{

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
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->warehousemanagement = $warehousemanagement;
        $this->vendorOrderCollectionFactory = $vendorOrderCollectionFactory;
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

            foreach ($data as $singleRaw) {
                $model = $this->warehousemanagement->create();
                $warehouseCollection = $model->getCollection()
                                        ->addFieldToFilter('product_location',$singleRaw['product_location'])
                                        ->addFieldToFilter('order_event',$singleRaw['order_event'])
                                        ->addFieldToFilter('barcode_number',$singleRaw['barcode_number'])
                                        ->addFieldToFilter('product_id',$singleRaw['product_id'])
                                        ->getData();
                /*...To check for duplicate barcode scan...*/
                if (empty($warehouseCollection)) {
                    $model->setData($singleRaw);
                    $model->save();
                } else {
                    $error['duplicateInDb'] = 0;
                    $resultJson->setData($error);
                    return $resultJson;
                }

                /*...To save Vendor order status...*/
                // $vendorOrders = $this->vendorOrderCollectionFactory->create()
                //     ->addFieldToFilter("increment_id", $singleRaw['sub_order_id']);
                // if ($singleRaw['product_location'] == 0) {
                //     $vendorOrderStatus = "Return to Warehouse";
                // } else {
                //     $vendorOrderStatus = "Return to Vendor";
                // }
                // foreach ($vendorOrders as $vendorOrder) {
                //     $vendorOrder->setData('status', $vendorOrderStatus);
                //     $vendorOrder->save();
                // }

                $msgTempFlag = $singleRaw['main_order_status'];
            }
            if ($msgTempFlag == 0) {
                $this->messageManager->addSuccessMessage(__('You saved vendor to warehouse record successfully.'));
            } else {
                $this->messageManager->addSuccessMessage(__('You saved warehouse to customer record successfully.'));
            }
            return $resultRedirect->setPath('*/*/');
        }
    }
}
