<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Controller\Sellerhtml\VendorRequest;

use Magento\Framework\Controller\ResultFactory;

class AddComment extends \Magedelight\Rma\Controller\Sellerhtml\VendorRequest
{
    
    /**
     *
     * @param \Magedelight\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Vendor\Model\Design $design
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Rma\Model\RmaFactory $rmaFactory
     * @param \Magento\Rma\Model\ItemFactory $rmaItemFactory
     */

    public function __construct(
        \Magedelight\Backend\App\Action\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Vendor\Model\Design $design,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        \Magento\Rma\Model\ItemFactory $rmaItemFactory
    ) {
        parent::__construct(
            $context,
            $authSession,
            $resultPageFactory,
            $design,
            $coreRegistry
        );
        $this->_rmaItemFactory = $rmaItemFactory;
        $this->_rmaFactory = $rmaFactory;
        $this->authSession = $authSession;
    }
//    public function __construct(
//        \Magedelight\Backend\App\Action\Context $context,
//        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
//        \Magedelight\Vendor\Model\Design $design,
//        \Magento\Framework\Registry $coreRegistry,
//        \Magento\Rma\Model\RmaFactory $rmaFactory,
//        \Magento\Rma\Model\ItemFactory $rmaItemFactory)
//    {
//        parent::__construct(
//            $context,
//            $resultPageFactory,
//            $design,
//            $coreRegistry);
//        $this->_vendorSession = $context->getSession();
//        $this->_rmaItemFactory = $rmaItemFactory;
//        $this->_rmaFactory = $rmaFactory;
//    }
    /**
     * Add RMA comment action
     *
     * @return void|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_loadValidRma()) {
            return;
        }
        
        try {
            $comment = $this->getRequest()->getPost('comment');
            $postStatus = trim($this->getRequest()->getPost('status'));
            $comment = trim(strip_tags($comment));
            if (empty($comment)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid message.'));
            }
            $rma = $this->_coreRegistry->registry('vendor_current_rma');

            /** @var $rma \Magento\Rma\Model\Rma */
            $vendorName = $rma->getVendorName();
            $rmaModel = $this->_rmaFactory->create()->load($rma->getId());
            if ($postStatus === "approved") {
                $rmaModel->setData('status', 'processed_closed');
            } elseif (($postStatus === "denied") || ($postStatus === "rejected")) {
                $rmaModel->setData('status', "closed");
            } elseif ($postStatus !== "") {
                $rmaModel->setData('status', $postStatus);
            }
            $rmaModel->save();
            
            $rmaItemArray = explode(',', $this->getRequest()->getPost('rmaItemArray'));
            foreach ($rmaItemArray as $rmaItemId) {
                $itemModel = $this->_rmaItemFactory->create()->load($rmaItemId);
                switch ($postStatus) {
                    case "authorized":
                        $itemModel->setData('qty_authorized', $itemModel->getQtyRequested());
                        break;
                    case "received":
                        $itemModel->setData('qty_returned', $itemModel->getQtyRequested());
                        break;
                    case "approved":
                        $itemModel->setData('qty_approved', $itemModel->getQtyRequested());
                        break;
                }
                $itemModel->setData('qty_authorized', $itemModel->getQtyRequested());
                $itemModel->setData('status', $postStatus);
                $itemModel->save();
            }
            
            /** @var $statusHistory \Magedelight\Rma\Model\Rma\Status\History */
            $statusHistory = $this->_objectManager->create(\Magedelight\Rma\Model\Rma\Status\History::class);
            $vendorId =  $this->authSession->getUser()->getVendorId();

            $statusHistory->setRmaEntityId($rma->getId());
            $statusHistory->setComment($comment);
            $statusHistory->setVendorId($vendorId);
            $statusHistory->setVendorName($vendorName);
            $statusHistory->setStatus($postStatus);
            $statusHistory->sendCustomerCommentEmail();
            $statusHistory->saveComment($comment, true, false);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Cannot add message.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/view', ['entity_id' => (int) $this->getRequest()->getParam('entity_id')]);
    }
}
