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

use Magedelight\Backend\App\Action\Context;
use Magedelight\Vendor\Api\AccountManagementInterface;
use Magedelight\Vendor\Model\AccountManagement;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author Rocket Bazaar Core Team
 * Created at 16 March, 2016 4:15 PM
 */
class ForgotPasswordPost extends \Magedelight\Backend\App\Action
{
    /** @var AccountManagementInterface */
    protected $vendorAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
     *
     * @param Context $context
     * @param AccountManagementInterface $vendorAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        AccountManagementInterface $vendorAccountManagement,
        Escaper $escaper
    ) {
        $this->vendorAccountManagement = $vendorAccountManagement;
        $this->escaper = $escaper;
        parent::__construct($context);
    }

    /**
     * Forgot vendor password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if (!$this->_session->isLoggedIn()) {
            if ($email) {
                if (!\Zend_Validate::is($email, 'EmailAddress')) {
                    $this->_session->setForgottenEmail($email);
                    $this->messageManager->addErrorMessage(__('Enter a valid email address.'));
                    return $resultRedirect->setPath('*/*/forgotpassword');
                }

                try {
                    $this->vendorAccountManagement->initiatePasswordReset(
                        $email,
                        AccountManagement::EMAIL_RESET
                    );
                    $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(__(
                        "Sorry, we didn't found any account associated with '%1'.",
                        $this->escaper->escapeHtml($email)
                    ));
                    return $resultRedirect->setPath('*/*/forgotpassword');
                } catch (AuthenticationException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __("We're unable to send the password reset email.")
                    );
                    return $resultRedirect->setPath('*/*/forgotpassword');
                }
                return $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addErrorMessage(__('Please enter your email.'));
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
        } else {
            return $resultRedirect->setPath('*/*/');
        }
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }

    protected function _isAllowed()
    {
        return true;
    }
}
