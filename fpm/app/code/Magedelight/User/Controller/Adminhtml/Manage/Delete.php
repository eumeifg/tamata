<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Controller\Adminhtml\Manage;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Delete extends \Magedelight\Vendor\Controller\Adminhtml\Index
{

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_User::delete_vendor_user');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $vendor = $this->_vendorRepository->getById((int)$this->getRequest()->getParam('vendor_id'));
        try {
            $eventParams = ['vendor_ids' => $this->getRequest()->getParam('vendor_id')];
            $this->_eventManager->dispatch('vendor_status_mass_delete_after', $eventParams);
            $vendor->delete();
            $this->messageManager->addSuccess(__('You have deleted user successfully .'));
            $this->_redirect('*/manage/index');
        } catch (Exception $ex) {
            $this->messageManager->addException($exception, __('Something went wrong while deleting the user.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->_redirect('*/*/index'));
            return $resultRedirect;
        }
    }
}
