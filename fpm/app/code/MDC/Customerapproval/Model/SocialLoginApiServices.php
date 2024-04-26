<?php

namespace MDC\Customerapproval\Model;

use Magedelight\SocialLogin\Api\SocialLoginApiServicesInterface;
use Magedelight\SocialLogin\Helper\Data as HelperData;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Math\Random;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Event\ManagerInterface;
use Magedelight\SocialLogin\Model\ResourceModel\Social\CollectionFactory as SocialLoginTypeCollection;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Customer\Model\AccountManagement as CustomerAccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Magedelight\SocialLogin\Model\SocialFactory;

class SocialLoginApiServices extends \Magedelight\SocialLogin\Model\SocialLoginApiServices
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;
    /**
     * @var RequestThrottler
     */
    private $requestThrottler;

    /**
     * @var Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var Magedelight\SocialLogin\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * SocialLoginApiServices constructor.
     * @param TokenModelFactory $tokenModelFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerAccountManagement $customerAccountManagement
     * @param CustomerFactory $customerFactory
     * @param HelperData $helperData
     * @param Random $mathRandom
     * @param \Magedelight\SocialLogin\Model\SocialFactory $socialFactory
     * @param SocialLoginTypeCollection $socialLoginTypeCollection
     * @param ManagerInterface|null $eventManager
     */

    public function __construct(
        TokenModelFactory $tokenModelFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerAccountManagement $customerAccountManagement,
        CustomerFactory $customerFactory,
        HelperData $helperData,
        Random $mathRandom,
        SocialFactory $socialFactory,
        SocialLoginTypeCollection $socialLoginTypeCollection,
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\Webapi\Rest\Request $request,
        ManagerInterface $eventManager = null
    ) {
        parent::__construct($tokenModelFactory, $customerRepository, $customerAccountManagement, $customerFactory, $helperData, $mathRandom, $socialFactory, $socialLoginTypeCollection, $customers, $request, $eventManager);
        $this->tokenModelFactory = $tokenModelFactory;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerFactory = $customerFactory;
        $this->helperData = $helperData;
        $this->mathRandom = $mathRandom;
        $this->socialLoginTypeCollection = $socialLoginTypeCollection;
        $this->socialFactory = $socialFactory;
        $this->customer = $customers;
        $this->request = $request;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
      
    }

    public function authenticateCustomerWithSocialLogin($input)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('try');
        $logger->info('JK Start');
        $logger->info(print_r($input,true));
        $logger->info('JK END');
        $socialId = $input['socialId'];
        $socialLoginType = $input['socialLoginType'];
        $input['flag'] = 0;        

        if($socialLoginType == "apple")
        {
           $socialDataCollection = $this->socialFactory->create()
                                          ->getCollection()
                                          ->addFieldToFilter('social_id', $socialId);

           $socialCollectionCount = $socialDataCollection->getSize();
           if ($socialCollectionCount > 0 ){

                foreach($socialDataCollection as $item){
                    $appleUSerCustomerId = $item->getCustomerId();
                }

                $customerCollectionForApple = $this->customer->getCollection()
                                                    ->addAttributeToFilter("entity_id", 
                                                            array("eq" => $appleUSerCustomerId));

                $customerCollectionCountForApple = $customerCollectionForApple->getSize();

                if($customerCollectionCountForApple > "0")
                {
                    foreach($customerCollectionForApple->getData() as $customeDetail)
                    {
                       $input['email'] = $customeDetail['email'];
                       $input['firstname'] =  $customeDetail['firstname'];
                       $input['lastname'] = $customeDetail['lastname'];
                    }
                } 

            }

            $socailIdFirst = explode(".",$socialId);
            if($input['firstname'] == '')
            {
                $input['firstname'] = "fname_".$socailIdFirst[0]; 
            }
            if($input['lastname'] == '')
            {
                $input['lastname'] = "lname_".$socailIdFirst[0]; 
            }             

        }
        if (!isset($input['email'])) {
            $input['flag'] = 1;
            $input['email'] = $socialId.'@'.$socialLoginType.".com";
        }

        $logger->info('datainput');
        $logger->info(print_r($input, true));
        $username = $input['email'];
        $logger->info('username'.$username);

        try {

            $logger->info('start-try');
            $customerCollection = $this->customer->getCollection()
                ->addAttributeToFilter("email", array("eq" => $username));
            $customerCollectionCount = $customerCollection->getSize();

            $logger->info('customerCollection');
            $logger->info($customerCollectionCount);
            if ($customerCollectionCount == "0"){
                     $logger->info('customerCollection == 0');
                // Create Customer & Set Customer Into Social Login Table
                $customerDataObject = $this->createNewCustomerWithSocialLogin($input);
            } else {
                $customer = $this->getCustomer($input['email']);

                 if (!empty($customer->getCustomAttributes())) {

                    if ($this->isAccountNotApproved($customer)) {
                      
                        throw new AuthenticationException(
                            __(
                                'Your account is not approved. Kindly contact website admin for assitance.'
                            )
                        );
                    }
                }
                $logger->info('else');
                // Get Customer First Object
                $customerDataObject = $customerCollection->getFirstItem();
                $existedCustomerId = $customerDataObject->getEntityId();
                $logger->info('existedCustomerId'.$existedCustomerId);

                $this->socialFactory->create()
                    ->setSocialId($socialId)
                    ->setUserEmail($username)
                    ->setType($socialLoginType)
                    ->setCustomerId($existedCustomerId)
                    ->save();

                $logger->info('socialFactory'.$socialId);
            }

            $logger->info('before catch');
            $username = $customerDataObject->getEmail();
            $logger->info('customerDataObject'.$username);
            $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
            $logger->info('getRequestThrottler');

        } catch (\Exception $e) {

            $logger->info('start-catch');
            $logger->info('Line No:138 - ' .$e->getMessage());
            
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            // throw new AuthenticationException(
            //     __(
            //         'The account sign-in was incorrect or your account is disabled temporarily. '
            //         . 'Please wait and try again later.'
            //     )
            // );
            throw new AuthenticationException(
                __($e->getMessage())
            );

            $logger->info('After Error');
        }
        $logger->info('After Catch');
        $this->eventManager->dispatch('customer_login', ['customer' => $customerDataObject]);
        $logger->info('After event');
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
         $logger->info('After event getRequestThrottler');
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }

    /**
     * Get request throttler instance
     *
     * @return RequestThrottler
     * @deprecated 100.0.4
     */
    private function getRequestThrottler()
    {
        if (!$this->requestThrottler instanceof RequestThrottler) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(RequestThrottler::class);
        }
        return $this->requestThrottler;
    }

    /**
     * @param $customerData
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createNewCustomerWithSocialLogin($customerData)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/createNewCustomerWithSocialLogin.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('createNewCustomerWithSocialLogin');
        $flag = $customerData['flag'];
        $email = $customerData['email'];
        $logger->info('email'.$email);
        $logger->info('flag'.$flag);
        /* Generate Password Hash */
        $hash = $this->customerAccountManagement->getPasswordHash($customerData['email']);
        $logger->info('hash'.$hash);
        /* Create Customer */
        try {

            $logger->info('try start');
            $customer = $this->customerFactory->create()
                ->setFirstname($customerData['firstname'])
                ->setLastname($customerData['lastname'])
                ->setEmail($email)
                ->setPassword($hash)
                ->save();
            $logger->info('customer Save');

        } catch (\Exception $e) {
          
            $logger->info('Line No:192 - ' .$e->getMessage());
            throw new AuthenticationException(__($e->getMessage()));
        }

        /* Sending Email To Customer */
        try{
            $logger->info('Sending Email To Customer');
            $newCustomerId = $customer->getId();
            $logger->info($newCustomerId);
            if (!$flag && $this->helperData->getWelcomePermission()) {
                $newLinkToken = $this->mathRandom->getUniqueHash();
                $customerRepo = $this->customerRepository->getById($newCustomerId);

                $this->customerAccountManagement->changeResetPasswordLinkToken($customerRepo, $newLinkToken);

                $customeremailData['name'] = trim($customerData['firstname'].' '.$customerData['lastname']);
                $customeremailData['email'] = trim($customerData['email']);
                $customeremailData['id'] = $newCustomerId;
                $customeremailData['rp_token'] = $newLinkToken;
                $this->helperData->sendWelcomeEmail($customeremailData);
            }
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Line No:215 - ' .$e->getMessage());
            throw new AuthenticationException(__($e->getMessage()));
        }

        /* Store data into magedelight_sociallogin table */
        try{
            $logger->info('Store data into magedelight_sociallogin table');
            $this->socialFactory->create()
                ->setSocialId($customerData['socialId'])
                ->setUserEmail($customerData['email'])
                ->setType($customerData['socialLoginType'])
                ->setCustomerId($newCustomerId)
                ->save();
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sociallogin.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Line No:231 - ' .$e->getMessage());
            throw new AuthenticationException(__($e->getMessage()));
        }
        return $customer;
    }

    /**
     * @return mixed|string
     * @throws AuthenticationException
     */
    public function authenticateCustomerWithSocialLoginWithApi()
    {
       $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/authenticateCustomerWithSocialLoginWithApi.log');
       $logger = new \Zend\Log\Logger();
       $logger->addWriter($writer);
       $logger->info('authenticateCustomerWithSocialLoginWithApi');
       $bodyParams = $this->request->getBodyParams();
       $logger->info(print_r($bodyParams,true));
       $token = $this->authenticateCustomerWithSocialLogin($bodyParams);
       return $token;
    }


    public function getCustomer($email)
    {
        try{
            $this->currentCustomer = $this->customerRepository->get($email);
            return $this->currentCustomer;
        }catch (\Exception $e){
            return false;
        }

    }

    /**
     * Check if customer is a vendor and account is approved
     * @return bool
     */
    public function isAccountNotApproved($customer)
    {

        $customAttribute = $customer->getCustomAttribute('approve_account');
        if(empty($customAttribute)){
            return true;
        }
        $isApprovedAccount = $customer->getCustomAttribute('approve_account')->getValue();
        if($isApprovedAccount)
        {
            return false;
        }
        return true;
    }
     
}