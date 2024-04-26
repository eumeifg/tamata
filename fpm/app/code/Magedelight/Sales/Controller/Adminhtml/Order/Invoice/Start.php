<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Controller\Adminhtml\Order\Invoice;

class Start extends \Magento\Sales\Controller\Adminhtml\Invoice\AbstractInvoice\View
{
    /**
     * Start create invoice action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
         * Clear old values for invoice qty's
         */
        $this->_getSession()->getInvoiceItemQtys(true);

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = ['order_id'=>$this->getRequest()->getParam('order_id')];
        if ($vendorId = $this->getRequest()->getParam('do_as_vendor')) {
            $params['do_as_vendor'] = $vendorId;
        }
        $params['vendor_order_id'] = $this->getRequest()->getParam('vendor_order_id');
        $resultRedirect->setPath('*/*/new', $params);
        return $resultRedirect;
    }
}
