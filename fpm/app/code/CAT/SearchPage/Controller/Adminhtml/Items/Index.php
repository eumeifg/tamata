<?php

namespace CAT\SearchPage\Controller\Adminhtml\Items;

class Index extends \CAT\SearchPage\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('CAT_SearchPage::search');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Search Page'));
        $resultPage->addBreadcrumb(__('Manage'), __('Manage'));
        $resultPage->addBreadcrumb(__('Search Page'), __('Search Page'));
        return $resultPage;
    }
}
