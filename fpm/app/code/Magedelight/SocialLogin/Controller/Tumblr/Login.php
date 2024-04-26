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





namespace Magedelight\SocialLogin\Controller\Tumblr;

use OAuth\OAuth1\Service\Tumblr;
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
        'consumerId' => $this->helper->getConsumerId('tumblr'),
        'consumerSecret' => $this->helper->getConsumerSecret('tumblr'),
        'callbackUrl' => $this->helper->getAuthUrl('tumblr')
        ]);
       
        $tumblrService = $this->oauthservicefactory->createService('tumblr', $credentials, $this->storage);

        $req_ouath_token = $this->getRequest()->getParam('oauth_token');
        $req_ouath_verifier = $this->getRequest()->getParam('oauth_verifier');
        $req_auth = $this->getRequest()->getParam('auth');

        $this->getRequest()->getParam('oauth_verifier');
        if (!empty($req_ouath_token)) {
            $token = $this->storage->retrieveAccessToken('Tumblr');

            // This was a callback request from tumblr, get the token
            $tumblrService->requestAccessToken(
                $req_ouath_token,
                $req_ouath_verifier,
                $token->getRequestTokenSecret()
            );

            // Send a request now that we have access token
            $result = json_decode($tumblrService->request('user/info'), true);

            $this->processResult((string) $result['response']['user']['name']);
        } elseif (!empty($req_auth) && $req_auth === '1/') {
            // extra request needed for oauth1 to request a request token :-)
            $token = $tumblrService->requestRequestToken();

            $url = $tumblrService->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);
            $this->getResponse()->setRedirect($url);
        }
    }

    private function processResult($result)
    {
        if (!isset($result)) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($result, 'tumblr');
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
                $email = $result.'@tumblr.com';
                $flag = 1;
                $data = ['firstname' => $result, 'lastname' => $result, 'email' => $email, 'store_id' => $store_id,
                'website_id' => $website_id, 'social_type' => 'tumblr', 'social_id' => $result, 'flag' => $flag];
                $this->_eventManager
                        ->dispatch('social_customer_create', ['customerdata' => $data]);

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
            }
        }
    }
}
