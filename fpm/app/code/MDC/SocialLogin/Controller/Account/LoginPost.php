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





namespace MDC\SocialLogin\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magedelight\SocialLogin\Controller\Account\LoginPost
{
    /**
     * Login post action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $this->layoutFactory = $this->_helper->getlayoutFactory();
        $this->resultJsonFactory = $this->_helper->getjsonFactory();
        $response = $this->_helper->getresponseObject();
        $response->setError(false);
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            $backUrl = $this->_helper->getBaseUrl().'customer/account';
            $response->setError(false);
            $response->setUrl($backUrl);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed.'.
                            ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                    $response->setError(true);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                    $response->setError(true);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Invalid login or password.'));
                    $response->setError(true);
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
                $response->setError(true);
            }
        }
        if ($response->getError() == true) {
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        } else {
            /*....To redirect based on click on login or go-to-checkot button....*/
            //$backUrl = $this->_helper->getRedirection();
            $backUrl = $login['currenturl'];
            $response->setUrl($backUrl);

            return $this->resultJsonFactory->create()->setJsonData($response->toJson());
        }
    }
}
