<?php

namespace CAT\VIP\Model;

use CAT\VIP\Api\VipCustomerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use CAT\VIP\Api\Data\VipCustomerDataInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory; 
use CAT\VIP\Helper\Data;
use CAT\VIP\Model\VipLogsFactory;

/**
 *
 */
class VipCustomer implements VipCustomerInterface
{
    const ORDER_COUNT_TO_BECOME_VIP = 'vip/customer/order_count';

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $vipOrderCollectionFactory;

    /**
     * @var VipCustomerDataInterfaceFactory
     */
    protected $vipCustomerDataFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    protected $_vipHelper;
    protected $_vipLogsFactory;
    protected $_customerFactory;

    /**
     * @param UserContextInterface $userContext
     * @param ScopeConfigInterface $scopeConfig
     * @param CollectionFactory $vipOrdersCollectionFactory
     * @param VipCustomerDataInterfaceFactory $vipCustomerDataFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        UserContextInterface $userContext,
        ScopeConfigInterface $scopeConfig,
        CollectionFactory $vipOrdersCollectionFactory,
        VipCustomerDataInterfaceFactory $vipCustomerDataFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        Data $vipHelper,
        VipLogsFactory $viplogsFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory

    )
    {
        $this->userContext = $userContext;
        $this->scopeConfig = $scopeConfig;
        $this->vipOrderCollectionFactory = $vipOrdersCollectionFactory;
        $this->vipCustomerDataFactory = $vipCustomerDataFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_vipHelper = $vipHelper;
        $this->_vipLogsFactory = $viplogsFactory;
        $this->_customerFactory = $customerFactory;
        $this->vipOrderCollection = $this->vipOrderCollectionFactory->create();
    }

    /**
     * @return \CAT\VIP\Api\Data\VipCustomerDataInterface|false
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getVipForCustomer()
    {
        if ($this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER) {
            $thresholdVipOrderCount = $this->getOrderCountToBecomeVip();
            $customerVipOrderCount = $this->getVipOrderCountsByCustomerId($this->userContext->getUserId());
            $_customer = $this->_customerRepositoryInterface->getById($this->userContext->getUserId());
            $isVip = $this->_vipHelper->isVipCustomer($_customer->getGroupId());
            $vipCustomerData = $this->vipCustomerDataFactory->create();
            $vipCustomerData->setIsVip($isVip)
                ->setThresholdVipOrderCount($thresholdVipOrderCount)
                ->setCustomerVipOrderCount($customerVipOrderCount);
            return $vipCustomerData;
        }
        return false;
    }

    public function getVipgetlastmonthorders(){
        $groupId = $this->_vipHelper->getVipGroupId();
        $customers = $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addFieldToFilter("group_id", array("eq" => $groupId))
                ->load();
        foreach($customers as $customer){
            $getcustomer = $customer->getID();
            $_customer = $this->_customerRepositoryInterface->getById($getcustomer);
            $thresholdVipOrderCount = $this->getOrderCountToBecomeVip();
            $customerVipOrderCount = $this->getLastmonthVipOrderCountsByCustomerId($getcustomer);
            if($thresholdVipOrderCount > $customerVipOrderCount){
                if ($_customer) {
                    $_customer->setGroupId(1);
                    $this->_customerRepositoryInterface->save($_customer);

                    // Assign Vip Status
                    $vipdata['customer_id'] = $getcustomer;
                    $vipdata['status'] = 0;
                    $model = $this->_vipLogsFactory->create();
                    $model->setData($vipdata);
                    $model->save();
                }
            }
           
        }
    }

    public function getVipgetYesterdayorders() {
        $acollection = $this->vipOrderCollectionFactory->create()->addFieldToSelect(['entity_id','customer_id']);
        $acollection->getSelect()->where(new \Zend_Db_Expr('updated_at between subdate(CURDATE(), 1) and date (now() )'));
        $acollection->getSelect()->where('status =?', 'complete');
        $orders = $acollection->getData();
        $groupId = $this->_vipHelper->getVipGroupId();
        foreach($orders as $order){
            $getcustomer = $order['customer_id'];
            $_customer = $this->_customerRepositoryInterface->getById($getcustomer);
            $isVip = $this->_vipHelper->isVipCustomer($_customer->getGroupId());
            if(!$isVip){    
                $thresholdVipOrderCount = $this->getOrderCountToBecomeVip();
                $customerVipOrderCount = $this->getVipOrderCountsByCustomerId($getcustomer);
                if($thresholdVipOrderCount <= $customerVipOrderCount){
                    if ($_customer) {
                        $_customer->setGroupId($groupId);
                        $this->_customerRepositoryInterface->save($_customer);

                        // Assign Vip Status
                        $vipdata['customer_id'] = $getcustomer;
                        $vipdata['status'] = 1;
                        $model = $this->_vipLogsFactory->create();
                        $model->setData($vipdata);
                        $model->save();
                    }
                }
            }
          
        }
    }

    /**
     * @param $customerId
     * @return int|void
     */

    public function getLastmonthVipOrderCountsByCustomerId($customerId) {
        $collection = $this->vipOrderCollectionFactory->create()->addFieldToSelect('entity_id');
        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $collection->getSelect()->where(new \Zend_Db_Expr('MONTH(`created_at`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)'));
        $collection->getSelect()->where(new \Zend_Db_Expr('YEAR(`created_at`) = YEAR(now())'));
        $collection->getSelect()->where('status =?', 'complete');
        /*echo $collection->getSelect(); die();*/
        if ($collection->getSize()) {
            $result = $collection->getColumnValues('entity_id');
            return count($result);
        }
        return 0;
    }

    public function getVipOrderCountsByCustomerId($customerId) {
        $collection = $this->vipOrderCollectionFactory->create()->addFieldToSelect('entity_id');
        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $collection->getSelect()->where(new \Zend_Db_Expr('MONTH(`created_at`) = MONTH(now())'));
        $collection->getSelect()->where(new \Zend_Db_Expr('YEAR(`created_at`) = YEAR(now())'));
        $collection->getSelect()->where('status =?', 'complete');
        /*echo $collection->getSelect(); die();*/
        if ($collection->getSize()) {
            $result = $collection->getColumnValues('entity_id');
            return count($result);
        }
        return 0;
    }

    /**
     * @return int
     */
    public function getOrderCountToBecomeVip() {
        return (int)$this->scopeConfig->getValue(self::ORDER_COUNT_TO_BECOME_VIP, ScopeInterface::SCOPE_STORE);
    }
}
