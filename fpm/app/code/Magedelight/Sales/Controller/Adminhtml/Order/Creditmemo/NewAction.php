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
namespace Magedelight\Sales\Controller\Adminhtml\Order\Creditmemo;

use Magento\Backend\App\Action;

class NewAction extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_creditmemo';

    /**
     * @var \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Action\Context $context
     * @param \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Action\Context $context,
        \Magedelight\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Creditmemo create page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $vendorId = $this->getRequest()->getParam('do_as_vendor');
        $this->creditmemoLoader->setOrderId($orderId);
        $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
        $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
        $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
        $this->creditmemoLoader->setVendorId($vendorId);
        $this->creditmemoLoader->setVendorOrderId($this->getRequest()->getParam('vendor_order_id'));
        $creditmemo = $this->creditmemoLoader->load(true);
        if ($creditmemo) {
            if ($comment = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true)) {
                $creditmemo->setCommentText($comment);
            }
            $resultPage = $this->resultPageFactory->create();
            $vendorId = $this->getRequest()->getParam('do_as_vendor');
            $resultPage->setActiveMenu('Magento_Sales::sales_order');
            $resultPage->getConfig()->getTitle()->prepend(__('Credit Memos'));
            if ($creditmemo->getInvoice()) {
                $resultPage->getConfig()->getTitle()->prepend(
                    __("New Memo for #%1", $creditmemo->getInvoice()->getIncrementId())
                );
            } else {
                $resultPage->getConfig()->getTitle()->prepend(__("New Memo"));
            }
            if ($creditmemo->getOrder()->getShippingMethod() != 'rbmatrixrate_rbmatrixrate') {
                $this->messageManager->addNoticeMessage(__("You need to manage shipping amount manually."));
            }
            return $resultPage;
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
