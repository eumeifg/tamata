<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MDC\Customerapproval\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Model\CredentialsValidator;
use Magento\Integration\Model\Oauth\Token as Token;
use Magento\Integration\Model\Oauth\TokenFactory as TokenModelFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Event\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * @inheritdoc
 */
class CustomerTokenService extends \Magento\Integration\Model\CustomerTokenService
{
    /**
     * Token Model
     *
     * @var TokenModelFactory
     */
    private $tokenModelFactory;

    /**
     * @var Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Customer Account Service
     *
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Integration\Model\CredentialsValidator
     */
    private $validatorHelper;

    /**
     * Token Collection Factory
     *
     * @var TokenCollectionFactory
     */
    private $tokenModelCollectionFactory;

    /**
     * @var RequestThrottler
     */
    private $requestThrottler;
    
    /** @var CustomerRepositoryInterface */
    protected $customerRepositoryInterface;

    /**
     * Initialize service
     *
     * @param TokenModelFactory $tokenModelFactory
     * @param AccountManagementInterface $accountManagement
     * @param TokenCollectionFactory $tokenModelCollectionFactory
     * @param \Magento\Integration\Model\CredentialsValidator $validatorHelper
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        TokenModelFactory $tokenModelFactory,
        AccountManagementInterface $accountManagement,
        TokenCollectionFactory $tokenModelCollectionFactory,
        CredentialsValidator $validatorHelper,
        ManagerInterface $eventManager = null,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        parent::__construct($tokenModelFactory, $accountManagement, $tokenModelCollectionFactory, $validatorHelper, $eventManager);
        $this->tokenModelFactory = $tokenModelFactory;
        $this->accountManagement = $accountManagement;
        $this->tokenModelCollectionFactory = $tokenModelCollectionFactory;
        $this->validatorHelper = $validatorHelper;
        $this->eventManager = $eventManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @inheritdoc
     */
    public function createCustomerAccessToken($username, $password)
    {
        $this->validatorHelper->validate($username, $password);
        $this->getRequestThrottler()->throttle($username, RequestThrottler::USER_TYPE_CUSTOMER);
        $customer = $this->getCustomer($username);
        
        if($customer){
            if (!empty($customer->getCustomAttributes())) {
                if ($this->isAccountNotApproved($customer)) {
                    throw new AuthenticationException(
                        __(
                            'Your account is not approved. Kindly contact website admin for assitance.'
                        )
                    );
                }
            }
        }
        
        
        try {
            
            $customerDataObject = $this->accountManagement->authenticate($username, $password);
        } catch (\Exception $e) {
            $this->getRequestThrottler()->logAuthenticationFailure($username, RequestThrottler::USER_TYPE_CUSTOMER);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        $this->eventManager->dispatch('customer_login', ['customer' => $customerDataObject]);
        $this->getRequestThrottler()->resetAuthenticationFailuresCount($username, RequestThrottler::USER_TYPE_CUSTOMER);
        return $this->tokenModelFactory->create()->createCustomerToken($customerDataObject->getId())->getToken();
    }
    
    /**
     * @param $email
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer($email)
    {
        try{
            $this->currentCustomer = $this->customerRepositoryInterface->get($email);
            return $this->currentCustomer;
        }catch (\Exception $e){
            return false;
        }

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