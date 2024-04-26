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

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        WarehousemanagementFactory $warehousemanagement
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->warehousemanagement = $warehousemanagement;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('warehousemanagement_id');

            $model = $this->warehousemanagement->create();
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Warehousemanagement no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Warehousemanagement.'));
                $this->dataPersistor->clear('ktpl_warehousemanagement_warehousemanagement');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['warehousemanagement_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Warehousemanagement.'));
            }

            $this->dataPersistor->set('ktpl_warehousemanagement_warehousemanagement', $data);
            return $resultRedirect->setPath('*/*/edit', ['warehousemanagement_id' => $this->getRequest()->getParam('warehousemanagement_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
