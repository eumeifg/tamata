<?php

namespace Magedelight\Abandonedcart\Block\Adminhtml\EmailQueue;

use \Magento\Framework\View\Element\Template;

class Preview extends Template
{
    /**
     * @var \Magedelight\Abandonedcart\Model\EmailQueueFactory
     */
    protected $emailqueueFactory;

    protected $resultRedirectFactory;
    /**
     * @param \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magedelight\Abandonedcart\Model\EmailQueueFactory $emailqueueFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        Template\Context $context,
        array $data = []
    ) {
        $this->emailqueueFactory = $emailqueueFactory;
        $this->resultRedirectFactory       = $resultRedirectFactory;
        parent::__construct($context, $data);
    }

    public function emailPreviewData()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $queueObj = $this->emailqueueFactory->create();
                $queueObj->load($id);
                return $queueObj;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/');
            }
        }
    }
}
