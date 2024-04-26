<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Controller\Adminhtml\Request;

use Magento\Framework\Controller\ResultFactory;

class MassDelete extends AbstractMassAction
{

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        /**
         * @var /RB/Vendor/Api/Data/VendorInterface
         */
        foreach ($collection as $vendorRequest) {
            $this->resetVendorRequest($vendorRequest->getVendorId());
            $vendorRequest->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::delete_request');
    }
    
    protected function resetVendorRequest($vendorId)
    {
            $vendorModel = $this->_vendorRepository->getById($vendorId);
            $vendorModel->setData('vacation_request_status', null);
            $vendorModel->setData('vacation_message', null);
//            $vendorModel->setData('vacation_from_date', null);
//            $vendorModel->setData('vacation_to_date', null);
            $vendorModel->save();
    }
}
