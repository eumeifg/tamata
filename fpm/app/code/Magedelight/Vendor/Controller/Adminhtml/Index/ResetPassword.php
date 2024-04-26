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
namespace Magedelight\Vendor\Controller\Adminhtml\Index;

class ResetPassword extends \Magedelight\Vendor\Controller\Adminhtml\Index
{
    /**
     * Reset password handler
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $vendorId = (int)$this->getRequest()->getParam('vendor_id', 0);
        if (!$vendorId) {
            $resultRedirect->setPath('vendor/index');
            return $resultRedirect;
        }

        try {
            $vendor = $this->_vendorRepository->getById($vendorId, $this->getRequest()->getParam('website_id'));
            $this->vendorAccountManagement->initiatePasswordReset(
                $vendor->getEmail(),
                \Magedelight\Vendor\Model\AccountManagement::EMAIL_REMINDER,
                $this->getRequest()->getParam('website_id')
            );
            $this->messageManager->addSuccess(__('The vendor will receive an email with a link to reset password.'));
        } catch (NoSuchEntityException $exception) {
            $resultRedirect->setPath('vendor/index');
            return $resultRedirect;
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $messages = $exception->getMessages(\Magento\Framework\Message\MessageInterface::TYPE_ERROR);
            if (!count($messages)) {
                $messages = $exception->getMessage();
            }
            $this->_addSessionErrorMessages($messages);
        } catch (\Exception $exception) {
            $this->messageManager->addException(
                $exception,
                __('Something went wrong while resetting vendor password.')
            );
        }
        $resultRedirect->setPath(
            'vendor/*/edit',
            ['id' => $vendorId,'website_id' => $this->getRequest()->getParam('website_id'), '_current' => true]
        );
        return $resultRedirect;
    }
}
