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

class EditReturn extends \Ktpl\Warehousemanagement\Controller\Adminhtml\Warehousemanagement
{
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ktpl\Warehousemanagement\Model\WarehousemanagementFactory $warehousemanagement
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->warehousemanagement = $warehousemanagement;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('warehousemanagement_id');
        $model = $this->warehousemanagement->create();

        // 2. Initial checking

        $this->_coreRegistry->register('ktpl_warehousemanagement_warehousemanagement', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Warehousemanagement') : __('New Warehousemanagement'),
            $id ? __('Edit Warehousemanagement') : __('New Warehousemanagement')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Warehousemanagements'));
        if ($this->getRequest()->getParam('deliverytype') == 0) :
            $resultPage->getConfig()->getTitle()->prepend(__('Product Return : From Customer to Warehouse'));
        else :
            $resultPage->getConfig()->getTitle()->prepend(__('Product Return : From Warehouse to Vendor'));
        endif;
        return $resultPage;
    }
}
