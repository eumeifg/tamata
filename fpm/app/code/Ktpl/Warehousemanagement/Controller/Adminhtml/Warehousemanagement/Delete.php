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

class Delete extends \Ktpl\Warehousemanagement\Controller\Adminhtml\Warehousemanagement
{

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Ktpl\Warehousemanagement\Model\WarehousemanagementFactory $warehousemanagement
    ) {
        $this->warehousemanagement = $warehousemanagement;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('warehousemanagement_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->warehousemanagement->create();
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['warehousemanagement_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Warehousemanagement to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
