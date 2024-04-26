<?php
 
namespace MDC\PickupPoints\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('MDC_PickupPoints::addpickups');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Pickup Point'));
        return $resultPage;
    }
}