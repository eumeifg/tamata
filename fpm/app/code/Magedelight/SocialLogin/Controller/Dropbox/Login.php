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





namespace Magedelight\SocialLogin\Controller\Dropbox;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Customer\Model\Session as Customersession;
use Magento\Customer\Model\CustomerFactory;
use OAuth\OAuth2\Service\Dropbox;
use OAuth\Common\Storage\Session\Proxy;
use OAuth\Common\Consumer\Credentials;

class Login extends Action
{
    public $dataHelper;
    public $customerUrl;
    public $session;
    public $customerFactory;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Dropboxhelper         $dropboxhelper
     * @param HelperData            $dataHelper
     * @param Customersession       $customerSession
     * @param CustomerFactory       $customerFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HelperData $dataHelper,
        Customersession $customerSession,
        CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger,
        \OAuth\Common\Storage\Session $oauthSession,
        \OAuth\ServiceFactory $oauthservicefactory,
        \OAuth\Common\Consumer\CredentialsFactory $credentialsFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
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
        'consumerId' => $this->helper->getConsumerId('dropbox'),
        'consumerSecret' => $this->helper->getConsumerSecret('dropbox'),
        'callbackUrl' => $this->helper->getAuthUrl('dropbox')
        ]);
              
        $dropboxService = $this->oauthservicefactory->createService('dropbox', $credentials, $this->storage, []);

        $req_code = $this->getRequest()->getParam('code');
        $req_auth = $this->getRequest()->getParam('auth');

        if (!empty($req_code)) {
            $token = $dropboxService->requestAccessToken($req_code);
            $result = json_decode($dropboxService->request('/account/info'), true);
            $data = ['id' => $result['uid'], 'firstname' => $result['name_details']['given_name'],
                'lastname' => $result['name_details']['surname'], 'email' => $result['email'] ];
            $this->processResult($data);
        } elseif (!empty($req_auth) && $req_auth === '1/') {
            $url = $dropboxService->getAuthorizationUri();
            $this->getResponse()->setRedirect($url);
        }
    }

    private function processResult($result)
    {
        if (!isset($result['id'])) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($result['id'], 'dropbox');
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
                $firstname = $result['firstname'];
                $lastname = $result['lastname'];
                if (!isset($result['email'])) {
                    $email = $firstname.'_'.$lastname.'@dropbox.com';
                    $flag = 1;
                } else {
                    $email = $result['email'];
                    $flag = 0;
                }
                $data = ['firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'store_id' => $store_id,
                'website_id' => $website_id, 'social_type' => 'dropbox', 'social_id' => $result['id'], 'flag' => $flag];
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
