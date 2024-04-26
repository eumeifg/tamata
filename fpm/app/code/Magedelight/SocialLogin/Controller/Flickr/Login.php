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





namespace Magedelight\SocialLogin\Controller\Flickr;

use OAuth\OAuth1\Service\Flickr;
use OAuth\Common\Storage\Session\Proxy;
use OAuth\Common\Consumer\Credentials;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Customer\Model\Session as Customersession;
use Magento\Customer\Model\CustomerFactory;

class Login extends Action
{
    public $flickr;
    public $helperData;
    public $customerUrl;
    public $session;
    public $customerFactory;

    public function __construct(
        Context $context,
        HelperData $dataHelper,
        CustomerFactory $customerFactory,
        Customersession $customerSession,
        \Psr\Log\LoggerInterface $logger,
        \OAuth\Common\Storage\Session $oauthSession,
        \OAuth\ServiceFactory $oauthservicefactory,
        \Magento\Framework\Filesystem\Io\File $systemFile,
        \OAuth\Common\Consumer\CredentialsFactory $credentialsFactory
    ) {
        parent::__construct($context);
        $this->helper = $dataHelper;
        $this->session = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->storage = $oauthSession;
        $this->oauthservicefactory = $oauthservicefactory;
        $this->fileSystem = $systemFile;
        $this->credentialsFactory = $credentialsFactory;
    }

    public function execute()
    {
       
        $credentials = $this->credentialsFactory->create([
        'consumerId' => $this->helper->getConsumerId('flickr'),
        'consumerSecret' => $this->helper->getConsumerSecret('flickr'),
        'callbackUrl' => $this->helper->getAuthUrl('flickr')
        ]);
          
        $flickrService = $this->oauthservicefactory->createService('Flickr', $credentials, $this->storage);

        $req_step = $this->getRequest()->getParam('step');
        $req_oauth_token = $this->getRequest()->getParam('oauth_token');
        $req_oauth_verifier = $this->getRequest()->getParam('oauth_verifier');

        $step = isset($req_step) ? (int) $req_step : null;
        $oauth_token = isset($req_oauth_token) ? $req_oauth_token : null;
        $oauth_verifier = isset($req_oauth_verifier) ? $req_oauth_verifier : null;
        if ($oauth_token && $oauth_verifier) {
            $step = 2;
        }
        switch ($step) {
            default:
                $this->getResponse()->setRedirect($this->helper->getLoginUrl('flickr'));
                break;
            case '1/':
                if ($token = $flickrService->requestRequestToken()) {
                    $oauth_token = $token->getAccessToken();
                    $secret = $token->getAccessTokenSecret();

                    if ($oauth_token && $secret) {
                        $url = $flickrService->getAuthorizationUri(['oauth_token' => $oauth_token, 'perms' => 'write']);
                        $this->getResponse()->setRedirect($url);
                    }
                }
                break;
            case 2:
                $token = $this->storage->retrieveAccessToken('Flickr');
                $secret = $token->getAccessTokenSecret();
                if ($token = $flickrService->requestAccessToken($oauth_token, $oauth_verifier, $secret)) {
                    $oauth_token = $token->getAccessToken();
                    $secret = $token->getAccessTokenSecret();
                    $this->storage->storeAccessToken('Flickr', $token);
                    $this->getResponse()->setRedirect($this->helper->getAuthUrl('flickr').'?step=3');
                }
                break;
            case 3:
                $xml = simplexml_load_string($flickrService->request('flickr.urls.getUserProfile'));
                $json = json_encode($xml);
                $data = json_decode($json, true);
                $nsid = (string) $data['user']['@attributes']['nsid'];
                break;
        }

        $this->getinfo($flickrService, $nsid);
    }
    // @codingStandardsIgnoreStart
    private function getinfo($flickrService, $nsid)
    {
    // @codingStandardsIgnoreEnd

        $url = 'https://api.flickr.com/services/rest/?method=flickr.people.getInfo';
        $url .= '&api_key='.$this->helper->getConsumerId('flickr');
        $url .= '&user_id='.$nsid;
        $url .= '&format=json';
        $url .= '&nojsoncallback=1';
        
        $response = json_decode($this->fileSystem->read($url), true);
        $email = $response['person']['username']['_content'].'@flickr.com';
        $name = explode(' ', $response['person']['realname']['_content']);
        $firstname = $name[0];
        if (isset($name[1])) {
            $lastname = $name[1];
        } else {
            $lastname = $nsid.'_lastname';
        }
        $flickrdata = ['id' => $nsid, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email];
        $this->processResult($flickrdata);
    }

    private function processResult($result)
    {
        if (!isset($result['id'])) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($result['id'], 'flickr');
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
                $email = $result['email'];
                $flag = 1;
                $data = ['firstname' => $result['firstname'], 'lastname' => $result['lastname'], 'email' => $email,
                'store_id' => $store_id, 'website_id' => $website_id, 'social_type' => 'flickr',
                'social_id' => $result['id'],'flag' => $flag];
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
