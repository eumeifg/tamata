<?php

namespace CAT\Bnpl\Helper;

use Magento\Framework\Event\Observer;
use CAT\Bnpl\Model\RestApiCall;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Quote\Api\CartManagementInterface;
class Data extends AbstractHelper
{

    protected $restApiCall;
    private $userContext;
    /**
     * @var Session
     */
    protected $_customerSession;
    protected $json;
    protected $customerRepository;
    private $cartManagement;

    /**
     * @param RestApiCall $restApiCall
     * @param Session $customerSession
     */
    public function __construct(
        UserContextInterface $userContext,
        RestApiCall $restApiCall,
        Session $customerSession,
        Json $json,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        CartManagementInterface $cartManagement
    ) {
        $this->userContext = $userContext;
        $this->restApiCall = $restApiCall;
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->cartManagement = $cartManagement;
    }

    public function isBnplAvailable(){
        $minorder = (float)$this->scopeConfig->getValue('payment/bnpl/minamount');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quote = $objectManager->get('\Magento\Checkout\Model\Session')->getQuote();
        $area  = $objectManager->get('Magento\Framework\App\State');
        $areaCode = $area->getAreaCode();
        if($areaCode == 'webapi_rest'){
            if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
                $customerId = $this->userContext->getUserId();
                $quote = $this->cartManagement->getCartForCustomer($customerId);
            }
        }
        if($minorder <= (float)$quote->getGrandTotal()){
            $customer = $this->customerRepository->getById($quote->getCustomer()->getID());
            $IsBnpl = $customer->getCustomAttribute('is_bnpl');
            if($IsBnpl && $IsBnpl->getValue()){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function getCustomerBnplBalance($email=null){
        if(!$email){
            $email = $this->_customerSession->getCustomer()->getEmail();
        }
        //$params = $this->json->serialize(['email' => 'mustafa_haider@tamata.com']);
        $params = $this->json->serialize(['email' => $email]);
        $response = $this->restApiCall->execute('getUserByEmail', 'GET', $params);
        if ($response['status']) {
            return $response['data'];
        }
    }

    public function createBnplLogs($Data,$info){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/bnpl.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($info);
        $logger->info(print_r($Data, true));
    }


}
