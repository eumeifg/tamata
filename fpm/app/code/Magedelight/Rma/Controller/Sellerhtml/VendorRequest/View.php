<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Controller\Sellerhtml\VendorRequest;

class View extends \Magedelight\Rma\Controller\Sellerhtml\VendorRequest
{
    /**
     * RMA view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_loadValidRma()) {
            $this->_redirect('*/*/index');
            return;
        }
            
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_objectManager->create(
            \Magento\Sales\Model\Order::class
        )->load(
            $this->_coreRegistry->registry('vendor_current_rma')->getOrderId()
        );
        
        $this->_coreRegistry->register('current_order', $order);

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(
            __('Return #%1', $this->_coreRegistry->registry('vendor_current_rma')->getIncrementId())
        );

        $this->_view->renderLayout();
    }
}
