<?php

namespace MDC\PickupPoints\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context        $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MDC_PickupPoints::addpickups');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Pickup Points'));
        return $resultPage;
    }
}