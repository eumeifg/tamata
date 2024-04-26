<?php

namespace CAT\GiftCard\Controller\Adminhtml\Rules\Coupon;

use CAT\GiftCard\Controller\Adminhtml\GiftCardRule;


class CouponsGrid extends GiftCardRule
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_initRule();
        //var_dump($this->_initRule()); die();
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Gift Card Rules'));
        $this->_view->renderLayout();
    }
}