<?php

namespace Ktpl\Tookan\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class Import extends Action implements HttpGetActionInterface
{
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Ktpl_Tookan::import');
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Ktpl\Tookan\Block\Adminhtml\Index\Import::class)
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Tookan Import'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ktpl_Tookan::import');
    }
}
