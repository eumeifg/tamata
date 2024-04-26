<?php

namespace CAT\VIP\Controller\Adminhtml\Logs;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\App\Action;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return bool|void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("CAT_VIP::logs");
    }

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        /*$resultPage->setActiveMenu('CAT_VIP::logs');*/
        $resultPage->getConfig()->getTitle()->prepend(__('VIP Logs'));
        /*$resultPage->addBreadcrumb(__('VIP'), __('VIP'));
        $resultPage->addBreadcrumb(__('Logs'), __('Logs'));*/
        return $resultPage;
    }
}
