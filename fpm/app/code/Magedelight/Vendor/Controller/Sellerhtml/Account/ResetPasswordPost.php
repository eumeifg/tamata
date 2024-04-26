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
namespace Magedelight\Vendor\Controller\Sellerhtml\Account;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magedelight\Vendor\Api\VendorRepositoryInterface;
use Magedelight\Backend\App\Action\Context;

/**
 * @author Rocket Bazaar Core Team
 * Created at 16 March, 2016 4:31 PM
 */
class ResetPasswordPost extends \Magedelight\Backend\App\Action
{
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var VendorRepositoryInterface */
    protected $vendorRepository;

    /**
     *
     * @param Context $context
     * @param AccountManagementInterface $accountManagement
     * @param VendorRepositoryInterface $vendorRepository
     */
    public function __construct(
        Context $context,
        AccountManagementInterface $accountManagement,
        VendorRepositoryInterface $vendorRepository
    ) {
        $this->accountManagement = $accountManagement;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resetPasswordToken = (string)$this->getRequest()->getQuery('token');
        $vendorId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');
        if (!$this->_session->isLoggedIn()) {
            if ($password !== $passwordConfirmation) {
                $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
                $resultRedirect->setPath('*/*/createPassword', ['id' => $vendorId, 'token' => $resetPasswordToken]);
                return $resultRedirect;
            }
            if (iconv_strlen($password) <= 0) {
                $this->messageManager->addError(__('Please enter a new password.'));
                $resultRedirect->setPath('*/*/createPassword', ['id' => $vendorId, 'token' => $resetPasswordToken]);
                return $resultRedirect;
            }

            try {
                $vendorEmail = $this->vendorRepository->getById($vendorId)->getEmail();
                $this->accountManagement->resetPassword($vendorEmail, $resetPasswordToken, $password);
                $this->_session->unsRpToken();
                $this->_session->unsRpVendorId();
                $this->messageManager->addSuccess(__('You updated your password.'));
                $resultRedirect->setPath('rbvendor');
                return $resultRedirect;
            } catch (\Exception $exception) {
                $this->messageManager->addError(__('Something went wrong while saving the new password.'));
                $resultRedirect->setPath('*/*/createPassword', ['id' => $vendorId, 'token' => $resetPasswordToken]);
                return $resultRedirect;
            }
        } else {
            return $resultRedirect->setPath('*/*/index');
        }
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
