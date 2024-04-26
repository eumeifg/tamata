<?php
 
namespace MDC\PickupPoints\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('MDC_PickupPoints::addpickups');
        $resultPage->getConfig()->getTitle()->prepend(__('Add New Pickup Point'));
        return $resultPage;
    }
}