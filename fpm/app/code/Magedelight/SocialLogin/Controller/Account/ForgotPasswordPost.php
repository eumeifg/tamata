<?php

/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>.
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_SocialLogin
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */





namespace Magedelight\SocialLogin\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session as customerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{

    public function __construct(
        Context $context,
        customerSession $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        \Magedelight\SocialLogin\Helper\Data $socialhelper
    ) {
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
        $this->_helper = $socialhelper;
    }
    /**
     * Forgot customer password action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->layoutFactory = $this->_helper->getlayoutFactory();
        $this->resultJsonFactory = $this->_helper->getjsonFactory();
        $response = $this->_helper->getresponseObject();
        $response->setError(false);
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(__('Please correct the email address.'));
                $response->setError(true);
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
                $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
                $response->setError(false);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong please try again!'));
                $response->setError(true);
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__($exception->getMessage()));
                $response->setError(true);
            }
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
        }
        $layout = $this->layoutFactory->create();
        $layout->initMessages();
        $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Retrieve success message.
     *
     * @param string $email
     *
     * @return \Magento\Framework\Phrase
     */
    // @codingStandardsIgnoreStart
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
    // @codingStandardsIgnoreEnd
}
