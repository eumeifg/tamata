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





namespace Magedelight\SocialLogin\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Customer\Model\CustomerFactory;
use Magedelight\SocialLogin\Model\SocialFactory;
use Magento\Customer\Model\Session;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Context as Actioncontext;
use Magento\Customer\Model\AccountManagement as CustomerAccountManagement;
use Magento\Framework\Math\Random;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SocialCustomer implements ObserverInterface
{
    private $customerFactory;
    private $logger;
    private $customerSession;
    private $socialFactory;
    private $helperData;
    private $storeManager;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerFactory          $customerFactory
     * @param SocialFactory            $socialFactory
     * @param Session                  $customerSession
     * @param HelperData               $helperData
     * @param StoreManagerInterface    $storeManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerFactory $customerFactory,
        SocialFactory $socialFactory,
        Session $customerSession,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        Actioncontext $actioncontext,
        CustomerAccountManagement $customerAccountManagement,
        Random $mathRandom,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        $this->session = $customerSession;
        $this->socialFactory = $socialFactory;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->actioncontext = $actioncontext;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->mathRandom = $mathRandom;
        $this->customerRepository = $customerRepository;
    }

    /*
     * Catches the events fired via events.xml
     */

    public function execute(EventObserver $observer)
    {
        $customerData = $observer->getEvent()->getCustomerdata();
        $redirectUrl = $this->helperData->getRedirection();
        try {
            $customerbyemail = $this->helperData
                                    ->getCustomerByEmail($customerData['email'], $customerData['website_id']);

            if (!empty($customerbyemail)) {
                $customerlogin = $this->customerFactory->create()->load($customerbyemail->getId());
                if ($customerlogin->getConfirmation()) {
                    try {
                        $customerlogin->setConfirmation(null);
                        $customerlogin->save();
                    } catch (\Exception $e) {
                        $this->logger->critical($e);
                    }
                }

                $this->session->setCustomerAsLoggedIn($customerlogin);
                $this->actioncontext->getResponse()->setBody('<script type="text/javascript">'
                        . 'try{window.opener.location.href="'.$redirectUrl.'";}'
                        . 'catch(e){window.opener.location.reload(true);} window.close();</script>');
            } else {
                $flag = $customerData['flag'];
                
                $store_id = $customerData['store_id'];
                $hash = $this->customerAccountManagement->getPasswordHash($customerData['email']);
                
                //Storing data in customer table
                $customer = $this->customerFactory->create()
                        ->setFirstname($customerData['firstname'])
                        ->setLastname($customerData['lastname'])
                        ->setEmail($customerData['email'])
                        ->setWebsiteId($customerData['website_id'])
                        ->setStoreId($store_id)
                        ->setPassword($hash)
                        ->save();
                $newcustomerid = $customer->getId();
                if (!$flag && $this->helperData->getWelcomePermission()) {
                    $customerrepo = $this->customerRepository->getById($customer->getId());
                    $newLinkToken = $this->mathRandom->getUniqueHash();
                    $this->customerAccountManagement->changeResetPasswordLinkToken($customerrepo, $newLinkToken);
                    $customeremailData['name'] = trim($customerData['firstname'].' '.$customerData['lastname']);
                    $customeremailData['email'] = trim($customerData['email']);
                    $customeremailData['id'] = $newcustomerid;
                    $customeremailData['rp_token'] = $newLinkToken;
                    $this->helperData->sendWelcomeEmail($customeremailData);
                }
                //Storing data in custom table
                $socialcustomer = $this->socialFactory->create()
                        ->setSocialId($customerData['social_id'])
                        ->setUserEmail($customerData['email'])
                        ->setType($customerData['social_type'])
                        ->setCustomerId($newcustomerid)
                        ->save();
                $this->session->setCustomerId($newcustomerid);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
