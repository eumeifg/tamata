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
namespace Magedelight\Sales\Controller\Sellerhtml\Order\Creditmemo;

use Magedelight\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;

class Add extends \Magedelight\Backend\App\Action
{

    /**
     * @var \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader
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
     * @var \Magedelight\Vendor\Model\Design
     */
    protected $design;
    /**
     * @var \Magedelight\Vendor\Model\Session
     */
    private $session;

    /**
     * @param Context $context
     * @param \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magedelight\Vendor\Model\Session $session
     */
    public function __construct(
        Context $context,
        \Magedelight\Sales\Controller\Sellerhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Vendor\Model\Design $design,
        \Magedelight\Vendor\Model\Session $session
    ) {
        $this->creditmemoLoader = $creditmemoLoader;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->design = $design;
        $this->authSession = $authSession;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * Creditmemo create page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $this->design->applyVendorDesign();
        $orderId = $this->getRequest()->getParam('order_id');
        $requestId = $this->getRequest()->getParam('request_id');
        $vendorId = $this->authSession->getUser()->getVendorId();
        $this->creditmemoLoader->setOrderId($orderId);
        $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
        $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
        $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
        $this->creditmemoLoader->setVendorId($vendorId);
        try {
            $creditmemo = $this->creditmemoLoader->load(true);
            if ($creditmemo) {
                if ($comment = $this->session->getCommentText(true)) {
                    $creditmemo->setCommentText($comment);
                }
                $resultPage = $this->resultPageFactory->create();
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
                $this->messageManager->addError(__("Failed to create credit memo."));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('rma/rma/requestview', ['id'=>$requestId]);
                return $resultRedirect;
            }
        } catch (\Exception $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(__("%1", $e->getMessage()));
            $resultRedirect->setPath('rma/rma/requestview', ['id'=>$requestId]);
            return $resultRedirect;
        }
    }
}
