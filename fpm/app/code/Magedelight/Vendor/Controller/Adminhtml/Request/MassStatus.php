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

class MassStatus extends AbstractMassAction
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Vendor::save_category_request');
    }

    /**
     * Update Vendor(s) Request status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $requestIds = $collection->getAllIds();
        $status = (int) $this->getRequest()->getParam('status');
        try {
            foreach ($collection as $request) {
                $vendor = $this->_vendorRepository->getById($request->getData('vendor_id'));
                $vendor->setData('vacation_request_status', $status)->save();
                $request->setStatus($status)->save();
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', count($requestIds)));
            /*   $this->_productPriceIndexerProcessor->reindexList($requestIds);*/
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->_getSession()->addException(
                $e,
                __('Something went wrong while updating the vendor(s) request status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
