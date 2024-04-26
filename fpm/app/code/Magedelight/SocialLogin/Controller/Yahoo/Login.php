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





namespace Magedelight\SocialLogin\Controller\Yahoo;

use Magedelight\SocialLogin\Controller\OpenId\LightOpenID as Openid;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Customer\Model\Session as customerSession;
use Magento\Customer\Model\CustomerFactory;

class Login extends Action
{
    public $helperData;
    public $customerUrl;
    public $session;
    public $customerFactory;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Yahoohelper           $helperYahoo
     * @param HelperData            $helperData
     * @param Session               $customerSession
     * @param CustomerFactory       $customerFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        customerSession $customerSession,
        CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->helper = $helperData;
        $this->session = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->request = $context->getRequest();
        $this->logger = $logger;
    }

    public function execute()
    {
        
        // @codingStandardsIgnoreStart
        $openid = new Openid($this->getBaseUrl(), null, $this, $this->request);
        // @codingStandardsIgnoreEnd
        $openid->identity = 'https://me.yahoo.com/';
        $openid->required = ['contact/email',
            'namePerson',
        ];
        $openid->returnUrl = $this->helper->getAuthUrl('yahoo');

        $req_auth = $this->getRequest()->getParam('auth');

        if (!empty($req_auth) && $req_auth === '1/') {
            $this->getResponse()->setRedirect($openid->authUrl());
        }

        if ($openid->mode) {
            if ($openid->mode == 'cancel') {
                $this->messageManager->addError(__('You have cancelled the authentication'));
                return $this->getResponse()->setBody("<script>window.close();window.opener.location = '"
                    .$this->getBaseUrl()."';</script>");
            } elseif ($openid->validate()) {
                $data = $openid->getAttributes();
                $id = substr($openid->identity, strpos($openid->identity, '#') + 1);
                $email = $data['contact/email'];
                $name = explode(' ', $data['namePerson']);
                $firstname = $name[0];
                $lastname = $name[1];
                $data = [
                    'id' => $id,
                    'first_name' => $firstname,
                    'last_name' => $lastname,
                    'email' => $email,
                ];
                $this->processResult($data);
            } else {
                return $this->getResponse()->setBody("<script>window.close();window.opener.location = '"
                    .$this->getBaseUrl()."';</script>");
            }
        } else {
            return $this->getResponse()->setBody("<script>window.close();window.opener.location = '"
                    .$this->getBaseUrl()."';</script>");
        }
    }

    private function processResult($result)
    {
        if (!isset($result['id'])) {
            $this->getResponse()->setBody('<script>window.close()</script>');
            $this->messageManager->addError(__('Sorry something went wrong please try again'));
            return false;
        } else {
            $customerId = $this->helper->getCustomerExist($result['id'], 'yahoo');
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
                if (!isset($result['email'])) {
                    $email = $result['id'].'@yahoo.com';
                    $flag = 1;
                } else {
                    $email = $result['email'];
                    $flag = 0;
                }
                $data = ['firstname' => $result['first_name'], 'lastname' => $result['last_name'],
                         'email' => $email,'store_id' => $store_id, 'website_id' => $website_id,
                         'social_type' => 'yahoo','social_id' => $result['id'], 'flag' => $flag];
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

    private function getBaseUrl()
    {
        return $this->storeManager->getStore()->getUrl();
    }
}
