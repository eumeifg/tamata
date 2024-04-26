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





namespace Magedelight\SocialLogin\Controller\Twitter;

use OAuth\OAuth2\Service\Twitter;
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
     * @param Twitterhelper   $Twitterhelper
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
        'consumerId' => $this->helper->getConsumerId('twitter'),
        'consumerSecret' => $this->helper->getConsumerSecret('twitter'),
        'callbackUrl' => $this->helper->getAuthUrl('twitter')
        ]);
        
        $twitterService = $this->oauthservicefactory->createService('twitter', $credentials, $this->storage);

        $req_ouath_token = $this->getRequest()->getParam('oauth_token');
        $req_ouath_verifier = $this->getRequest()->getParam('oauth_verifier');
        $req_auth = $this->getRequest()->getParam('auth');

        if (!empty($req_ouath_token)) {
            $token = $this->storage->retrieveAccessToken('Twitter');
            // This was a callback request from twitter, get the token
            $twitterService->requestAccessToken(
                $req_ouath_token,
                $req_ouath_verifier,
                $token->getRequestTokenSecret()
            );
            // Send a request now that we have access token
            $result = json_decode($twitterService->request('account/verify_credentials.json'));
            $this->processResult($result);
        } elseif (!empty($req_auth) && $req_auth === '1/') {
            $token = $twitterService->requestRequestToken();
            $url = $twitterService->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);
            $this->getResponse()->setRedirect($url);
        }
    }

    private function getBaseUrl()
    {
        return $this->storeManager->getStore()->getUrl();
    }

    private function processResult($result)
    {
        if (!isset($result->id)) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($result->id, 'twitter');
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
                if (!isset($result->email)) {
                    $email = $result->screen_name.'@twitter.com';
                    $flag = 1;
                } else {
                    $email = $result->email;
                    $flag = 0;
                }

                $data = ['firstname' => $result->name, 'lastname' => $result->name, 'email' => $email,
                'store_id' => $store_id, 'website_id' => $website_id, 'social_type' => 'twitter',
                'social_id' => $result->id,'flag' => $flag];
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
