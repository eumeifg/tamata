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

class InlineEdit extends \Magento\Backend\App\Action
{

    protected $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Ktpl\Warehousemanagement\Model\WarehousemanagementFactory $warehousemanagement
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->warehousemanagement = $warehousemanagement;
    }

    /**
     * Inline edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        //echo "here";die();
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            //echo "<pre>";print_r($postItems);exit;
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $modelid) {
                    /** @var \Ktpl\Warehousemanagement\Model\Warehousemanagement $model */
                    $model = $this->warehousemanagement->create();
                    $model->load($modelid);
                    //echo "<pre>";print_r($model->getData()); print_r($postItems[$modelid]);exit;
                    try {
                        $model->setData(array_merge($model->getData(), $postItems[$modelid]));
                        $model->save();
                    } catch (\Exception $e) {
                        $messages[] = "[Warehousemanagement ID: {$modelid}]  {$e->getMessage()}";
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
