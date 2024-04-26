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





namespace Magedelight\SocialLogin\Controller\Instagram;

use OAuth\OAuth2\Service\Instagram;
use OAuth\Common\Storage\Session\Proxy;
use OAuth\Common\Consumer\Credentials;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Customer\Model\Session as Customersession;
use Magento\Customer\Model\CustomerFactory;

class Login extends Action
{
    public $dataHelper;
    public $customerSession;
    public $customerFactory;

    /**
     * @param Context         $context
     * @param HelperData      $dataHelper
     * @param CustomerFactory $customerFactory
     * @param Customersession $customerSession
     * @param Instagramhelper $Instagramhelper
     */
    public function __construct(
        Context $context,
        HelperData $dataHelper,
        CustomerFactory $customerFactory,
        Customersession $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \OAuth\Common\Storage\Session $oauthSession,
        \OAuth\ServiceFactory $oauthservicefactory,
        \OAuth\Common\Consumer\CredentialsFactory $credentialsFactory
    ) {
        parent::__construct($context);
        $this->helper = $dataHelper;
        $this->session = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->storage = $oauthSession;
        $this->oauthservicefactory = $oauthservicefactory;
        $this->credentialsFactory = $credentialsFactory;
    }

    public function execute()
    {
        
        $credentials = $this->credentialsFactory->create([
        'consumerId' => $this->helper->getConsumerId('instagram'),
        'consumerSecret' => $this->helper->getConsumerSecret('instagram'),
        'callbackUrl' => $this->helper->getAuthUrl('instagram')
        ]);
        
        $scopes = ['basic'];

        $req_code = $this->getRequest()->getParam('code');
        $req_state = $this->getRequest()->getParam('state');
        $req_auth = $this->getRequest()->getParam('auth');
        /** @var $instagramService Instagram */
        $instagramService = $this->oauthservicefactory->createService(
            'instagram',
            $credentials,
            $this->storage,
            $scopes
        );
        if (!empty($req_code)) {
            // retrieve the CSRF state parameter
            $state = isset($req_state) ? $req_state : null;
            // This was a callback request from Instagram, get the token
            $instagramService->requestAccessToken($req_code, $state);
            // Send a request with it
            $result = json_decode($instagramService->request('users/self'), true);
            // Show some of the resultant data
            $this->processResult($result);
        } elseif (!empty($req_auth) && $req_auth === '1/') {
            $url = $instagramService->getAuthorizationUri();
            $this->getResponse()->setRedirect($url);
        }
    }

    private function processResult($result)
    {
        $id = $result['data']['id'];
        if (!isset($id)) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($id, 'instagram');
            if (!empty($customerId)) {
                $customer = $this->customerFactory->create()->load($customerId);
                if ($customer->getConfirmation()) {
                    try {
                        $customer->setConfirmation(null);
                        $customer->save();
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }
                $this->session->setCustomerAsLoggedIn($customer);
                return $this->getResponse()->setBody('<script type="text/javascript">
                    try{window.opener.location.href="'.$this->helper->getRedirection().'";}
                    catch(e){window.opener.location.reload(true);} window.close();</script>');
            } else {
                $store_id = $this->helper->getStoreId();
                $website_id = $this->helper->getWebsiteId();
                $flag = 0;
                if (!isset($result['data']['email'])) {
                    $email = $result['data']['username'].'@instagram.com';
                    $flag = 1;
                } else {
                    $email = $result['data']['email'];
                    $flag = 0;
                }
                $data = [
                    'firstname' => $result['data']['full_name'],
                    'lastname' => $result['data']['full_name'].'_lastname',
                    'email' => $email, 'store_id' => $store_id,
                    'website_id' => $website_id,
                    'social_type' => 'instagram', 'social_id' => $id, 'flag' => $flag];
                $this->_eventManager
                        ->dispatch('social_customer_create', ['customerdata' => $data]);
                if ($flag) {
                    $customerId = $this->session->getCustomerId();
                    $customer = $this->customerFactory->create()->load($customerId);
                    if ($customer->getConfirmation()) {
                        try {
                            $customer->setConfirmation(null);
                            $customer->save();
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                    $this->session->setCustomerAsLoggedIn($customer);
                    $this->session->regenerateId();
                    $this->messageManager->addNotice(__('Please update your Email id and Password'));
                    $this->messageManager->addNotice(__('Hint:Your temporary email id is your password.'));
                    return $this->getResponse()->setBody("<script>window.close();window.opener.location = '"
                    .$this->helper->customerEditUrl()."';</script>");
                } else {
                    $this->messageManager->addNotice(__('Please Change Your Password'));
                    $this->messageManager->addNotice(__('Hint:Your email id is your password.'));
                    return $this->getResponse()->setBody('<script type="text/javascript">
                    try{window.opener.location.href="'.$this->helper->getRedirection().'";}
                    catch(e){window.opener.location.reload(true);} window.close();</script>');
                }
            }
        }
    }
}
