<?php

namespace CAT\GiftCard\Plugin\Customer;

use CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class IndexPlugin
 * @package CAT\GiftCard\Plugin\Customer
 */
class IndexPlugin
{
    /**
     * @var CollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * IndexPlugin constructor.
     * @param CollectionFactory $couponCollectionFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param BalanceFactory $balanceFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CollectionFactory $couponCollectionFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        BalanceFactory $balanceFactory,
        CustomerRepositoryInterface $customerRepository,
        ManagerInterface $messageManager
    ) {
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_balanceFactory = $balanceFactory;
        $this->customerRepository = $customerRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\GiftCardAccount\Controller\Customer\Index $subject
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeExecute(\CAT\GiftCard\Rewrite\Customer\IndexRewrite $subject) {
        //echo "<pre>"; print_r($subject->getRequest()->getParam('giftcard_code')); echo "</pre>";
        //echo "<pre>"; print_r($subject->getRequest()->isPost()); echo "</pre>";
//        $this->messageManager->addSuccessMessage('dsnc ds vkjds j');
//        $subject->getRequest()->setParams(['giftcard_code'=> '']);
//        return;
        if(!$this->validateGiftCard($subject)) {
            $subject->getRequest()->setParams(['giftcard_code'=> null]);
            return;
        }
        if ($this->redeemGiftCard($subject)) {
            return;
        }
    }

    /**
     * @param $subject
     * @return bool
     */
    public function validateGiftCard($subject) {
        $customerId = $this->customerSession->getCustomer()->getId();
        if($subject->getRequest()->getParam('giftcard_code')) {
            $collection = $this->couponCollectionFactory->create();
            $collection->addFieldToFilter('code', ['eq' => $subject->getRequest()->getParam('giftcard_code')]);
            if ($collection->getSize()) {
                $collection->getSelect()
                    ->joinLeft(['gift_customer' => 'giftcard_customer'], 'gift_customer.rule_id = main_table.rule_id')
                    ->joinLeft(['gift_rule' => 'giftcard_rule'], 'gift_rule.rule_id = main_table.rule_id');
                $data = $collection->getFirstItem();
                if (!$data->getIsActive()) {
                    return false;
                }
                if ($data->getCustomerId() === $customerId){
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $subject
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function redeemGiftCard($subject) {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('code', ['eq' => $subject->getRequest()->getParam('giftcard_code')]);
        $collection->getSelect()->joinLeft(['gift_rule' => 'giftcard_rule'], 'gift_rule.rule_id = main_table.rule_id',
            ['gift_card_rule_id' => 'rule_id', 'discount_amount']
        );
        if ($collection->getSize()) {
            $ruleData = $collection->getFirstItem();
            //echo "<pre>"; print_r($ruleData->getData()); echo "</pre>";
            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
            $currentWebsiteId = !empty($currentWebsiteId) ? $currentWebsiteId : 1;
            try {
                $customerId = $this->customerSession->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);
                $balance = $this->_balanceFactory->create();
                $balance->setCustomer($customer)
                    ->setWebsiteId($currentWebsiteId)
                    ->setAmountDelta($ruleData->getDiscountAmount())
                    ->setHistoryAction(
                        \Magento\CustomerBalance\Model\Balance\History::ACTION_UPDATED
                    )
                    ->setUpdatedActionAdditionalInfo(__('Amount added for the gift card code %1', $ruleData->getCode()))
                    ->save();
                return true;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}