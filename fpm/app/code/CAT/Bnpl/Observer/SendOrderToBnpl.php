<?php

namespace CAT\Bnpl\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CAT\Bnpl\Model\RestApiCall;
use Magento\Customer\Api\CustomerRepositoryInterface;

class SendOrderToBnpl implements ObserverInterface
{
    /**
     * @var RestApiCall
     */
    protected $restApiCall;
    protected $customerRepository;

    /**
     * @param RestApiCall $restApiCall
     */
    public function __construct(
        RestApiCall $restApiCall,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->restApiCall = $restApiCall;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();
        if ($methodCode == 'bnpl') {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $bnpl_customer_id = $customer->getCustomAttribute('bnpl_customer_id');
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $BnplHelper = $objectManager->create(\CAT\Bnpl\Helper\Data::class);
            $BnplData = $BnplHelper->getCustomerBnplBalance($customer->getEmail());
            $BnplHelper->createBnplLogs($BnplData,'Response From balance');
            if($bnpl_customer_id && $bnpl_customer_id->getValue() && isset($BnplData['availableBalance'])){
                if((float)$order->getGrandTotal() >  (float)$BnplData['availableBalance']) {
                     $initbalance = ((float)$order->getGrandTotal() - (float)$BnplData['availableBalance']);
                }
                else{
                    $initbalance = 0;
                } 
                $params = ['customerId' => $bnpl_customer_id->getValue(), 'orderId' => $order->getIncrementId(), 'netAmount' => $order->getGrandTotal(), 'initPayment' => $initbalance];

                $BnplHelper->createBnplLogs($params,'Request Perms');
                $response = $this->restApiCall->execute('createOrder', 'POST', json_encode($params), '');
                $BnplHelper->createBnplLogs($response,'Request response');
            }
            else{
                $BnplHelper->createBnplLogs([],$order->getIncrementId().' Customer ID not found');
            }
        }
    }
}
