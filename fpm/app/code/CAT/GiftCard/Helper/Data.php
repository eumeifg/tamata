<?php

namespace CAT\GiftCard\Helper;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\CustomerBalance\Model\Balance\History;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory;
use CAT\GiftCard\Model\GiftCardRuleFactory;
use Magento\Framework\App\ResourceConnection;
use CAT\GiftCard\Model\Rule\CustomerFactory as RuleCustomerFactory;
use Magento\GiftCardAccountGraphQl\Model\Money\Formatter as MoneyFormatter;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class Data extends AbstractHelper
{
    /**
     * @var StoreManager
     */
    protected $_storeManager;

    /**
     * @var BalanceFactory
     */
    protected $_balanceFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * @var GiftCardRuleFactory
     */
    protected $giftCardRuleFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var RuleCustomerFactory
     */
    protected $ruleCustomerFactory;

    /**
     * @var MoneyFormatter
     */
    private $moneyFormatter;
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param Context $context
     * @param StoreManager $storeManager
     * @param BalanceFactory $balanceFactory
     * @param ManagerInterface $messageManager
     * @param CustomerRepository $customerRepository
     * @param CollectionFactory $couponCollectionFactory
     * @param GiftCardRuleFactory $giftCardRuleFactory
     * @param ResourceConnection $resourceConnection
     * @param RuleCustomerFactory $ruleCustomerFactory
     * @param MoneyFormatter $moneyFormatter
     */
    public function __construct(
        Context             $context,
        StoreManager        $storeManager,
        BalanceFactory      $balanceFactory,
        ManagerInterface    $messageManager,
        CustomerRepository  $customerRepository,
        CollectionFactory   $couponCollectionFactory,
        GiftCardRuleFactory $giftCardRuleFactory,
        ResourceConnection  $resourceConnection,
        RuleCustomerFactory $ruleCustomerFactory,
        MoneyFormatter      $moneyFormatter
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_balanceFactory = $balanceFactory;
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->giftCardRuleFactory = $giftCardRuleFactory;
        $this->resourceConnection = $resourceConnection;
        $this->ruleCustomerFactory = $ruleCustomerFactory;
        $this->moneyFormatter = $moneyFormatter;
        $this->connection = $resourceConnection->getConnection();
    }

    public function validateGiftCardV2($code, $customerId): bool
    {
        $conn = $this->connection;
        $conn->beginTransaction();
        try {
            $select = $conn->select()->from(['gcc' => 'giftcard_coupon'])
                ->joinLeft(['gift_rule' => 'giftcard_rule'], 'gift_rule.rule_id = gcc.rule_id', ['rule_id', 'from_date', 'to_date', 'is_active', 'discount_amount', 'gift_times_used' => 'times_used', 'uses_per_coupon', 'uses_per_customer'])
                ->joinLeft(['gcu' => 'giftcard_coupon_usage'], 'gcu.coupon_id = gcc.coupon_id', ['gcu_customer_id' => 'customer_id', 'gcu_times_used' => 'times_used'])
                ->forUpdate()
                ->where('code=?', $code);

            $res = $conn->fetchAll($select);

            if (count($res) > 1) {
                $select->where('gcu.customer_id=?', $customerId);
            }
            $res1 = $conn->fetchAll($select);
            $res2 = end($res1);

            $varienObject = new DataObject();
            $couponObj = $varienObject->setData($res2);

            if (!$couponObj->getIsActive()) {
                $conn->commit();
                return false;
            }

            if (!empty($couponObj->getToDate()) && (strtotime(date('Y-m-d h:i:s')) > strtotime($couponObj->getToDate()))) {
                $conn->commit();
                return false;
            }

            $couponTimesUsed = $couponObj->getTimesUsed() == null ? 0 : $couponObj->getTimesUsed();
            if ($couponObj->getUsageLimit() <= $couponTimesUsed) {
                $conn->commit();
                return false;
            }

            if ($this->checkIfCustomerUsedRule($couponObj->getRuleId(), $customerId)) {
                if ($couponObj->getGcuCustomerId() == $customerId) {
                    $conn->commit();
                    return false;
                }
                if ($couponObj->getUsagePerCustomer() == $couponObj->getGcuTimesUsed()) {
                    $conn->commit();
                    return false;
                }
            } else {
                $conn->commit();
                return false;
            }
            $this->logger()->info("Start redemption for coupon " . $code);
            if ($this->redeemGiftCardV2($conn, $couponObj, $customerId)) {
                $conn->commit();
                $this->logger()->info("Complete redemption for coupon " . $code);
                return true;
            }
        } catch (Exception $e) {
            $this->logger()->info($e->getMessage());
            $conn->rollBack();
        }
        return false;
    }

    /*public function validateGiftCard($code, $customerId): bool
    {
        if ($code) {
            $collection = $this->couponCollectionFactory->create();
            $collection->addFieldToFilter('code', ['eq' => trim($code)]);
            $collection->getSelect()->joinLeft(
                ['gift_rule' => 'giftcard_rule'],
                'gift_rule.rule_id = main_table.rule_id',
                ['rule_id', 'rule_name', 'description', 'from_date', 'to_date', 'is_active', 'discount_amount',
                    'gift_times_used' => 'times_used', 'uses_per_coupon', 'uses_per_customer']
            );
            $collection->getSelect()->joinLeft(
                ['gcu' => 'giftcard_coupon_usage'],
                'gcu.coupon_id = main_table.coupon_id',
                ['gcu_customer_id' => 'customer_id', 'gcu_times_used' => 'times_used']
            )->forUpdate();

            //echo $collection->getSelect(); die();

            //$collection->getSelect()->joinLeft(['gc' => 'giftcard_customer'], 'gc.rule_id = main_table.rule_id', ['gc_rule_id' => 'rule_id', 'gc_customer_id' => 'customer_id', 'gc_times_used' => 'times_used']);
            if ($collection->getSize()) {
                if ($collection->getSize() > 1) {
                    $collection->getSelect()->where('gcu.customer_id=?', $customerId);
                }
                $couponRules = $collection->getFirstItem();
                if (!$couponRules->getIsActive()) {
                    return false;
                }
                if (!empty($couponRules->getToDate()) && (strtotime(date('Y-m-d h:i:s')) > strtotime($couponRules->getToDate()))) {
                    return false;
                }
                $couponTimesUsed = $couponRules->getTimesUsed() == null ? 0 : $couponRules->getTimesUsed();
                if ($couponRules->getUsageLimit() <= $couponTimesUsed) {
                    return false;
                }

                if ($this->checkIfCustomerUsedRule($couponRules->getRuleId(), $customerId)) {
                    if ($couponRules->getGcuCustomerId() == $customerId) {
                        return false;
                    }
                    if ($couponRules->getUsagePerCustomer() == $couponRules->getGcuTimesUsed()) {
                        return false;
                    }
                } else {
                    return false;
                }
                if ($this->redeemGiftCardV2($couponRules, $customerId)) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }*/

    public function checkIfCustomerUsedRule($ruleId, $customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $query = $connection->select()
            ->from(['gr' => 'giftcard_rule'], 'gr.uses_per_customer')
            ->joinLeft(['gc' => 'giftcard_customer'], 'gc.rule_id = gr.rule_id', 'gc.times_used')
            ->where('gc.rule_id=?', $ruleId)
            ->where('gc.customer_id=?', $customerId);
        $result = $connection->fetchRow($query);
        if (empty($result) || $result['uses_per_customer'] > $result['times_used']) {
            return true;
        }
        return false;
    }


    /*public function redeemGiftCard($code, $customerId)
    {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('code', ['eq' => $code]);
        $collection->getSelect()->joinLeft(['gift_rule' => 'giftcard_rule'], 'gift_rule.rule_id = main_table.rule_id',
            ['gift_card_rule_id' => 'rule_id', 'discount_amount']
        );

        if ($collection->getSize()) {
            $ruleData = $collection->getFirstItem();
            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
            $currentWebsiteId = !empty($currentWebsiteId) ? $currentWebsiteId : 1;
            try {
                $customer = $this->customerRepository->getById($customerId);
                $balance = $this->_balanceFactory->create();
                $balance->setCustomer($customer)
                    ->setWebsiteId($currentWebsiteId)
                    ->setAmountDelta($ruleData->getDiscountAmount())
                    ->setHistoryAction(
                        History::ACTION_UPDATED
                    )
                    ->setUpdatedActionAdditionalInfo(__('Amount added for the gift card code %1', $ruleData->getCode()))
                    ->save();
                if ($ruleData->getTimesUsed() >= 0) {
                    $ruleData->setTimesUsed($ruleData->getTimesUsed() + 1)->save();
                }
                $this->updateUsedCouponDetails(true, $ruleData, $customerId, $this->connection);
                return true;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }*/

    /**
     * @param AdapterInterface $connection
     * @param $couponObj
     * @param $customerId
     * @return bool
     */
    public function redeemGiftCardV2(AdapterInterface $connection, $couponObj, $customerId): bool
    {
        $this->logger()->info('Redeem Start for coupon ' . $couponObj->getCode());
        try {
            if ($couponObj->getTimesUsed() >= 0) {
                $where = 'coupon_id = ' . $couponObj->getCouponId();
                $connection->update('giftcard_coupon', ['times_used' => $couponObj->getTimesUsed() + 1], $where);
                //$couponObj->setTimesUsed($couponObj->getTimesUsed() + 1)->save();
            }
            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();
            $currentWebsiteId = !empty($currentWebsiteId) ? $currentWebsiteId : 1;
            /** @var Customer $customer */
            $customer = $this->customerRepository->getById($customerId);
            $balance = $this->_balanceFactory->create();
            $balance->setCustomer($customer)
                ->setWebsiteId($currentWebsiteId)
                ->setAmountDelta($couponObj->getDiscountAmount())
                ->setHistoryAction(
                    History::ACTION_UPDATED
                )
                ->setUpdatedActionAdditionalInfo(__('Amount added for the gift card code %1', $couponObj->getCode()))
                ->save();

            $this->updateUsedCouponDetails(true, $couponObj, $customerId, $connection);
            $this->logger()->info('Redeem Completed for coupon ' . $couponObj->getCode() . ' with update all records');
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger()->info('Error NoSuchEntityException: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->logger()->info('Error Exception: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * @param $increment
     * @param $ruleData
     * @param $customerId
     * @param AdapterInterface $connection
     * @return void
     * @throws Exception
     */
    public function updateUsedCouponDetails($increment, $ruleData, $customerId, AdapterInterface $connection)
    {
        /*update the rule used time*/
        $ruleId = $ruleData->getRuleId();
        $giftCardRule = $this->giftCardRuleFactory->create()->load($ruleId);
        $giftCardRule->setTimesUsed($giftCardRule->getTimesUsed())->save();
        /*Update customer coupon used*/
        $ruleCustomer = $this->ruleCustomerFactory->create();
        $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
        if ($ruleCustomer->getId()) {
            if ($increment || $ruleCustomer->getTimesUsed() > 0) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() + ($increment ? 1 : -1));
            }
        } elseif ($increment) {
            $ruleCustomer->setCustomerId($customerId)->setRuleId($ruleId)->setTimesUsed(1);
        }
        $ruleCustomer->save();
        //$this->customerUsage->loadByCustomerRule($customerId, $ruleData->getRuleId());
        $connection->insert('giftcard_coupon_usage', ['coupon_id' => $ruleData->getCouponId(), 'customer_id' => $customerId, 'times_used' => '1']);
    }

    /*public function quickCheckCustomGiftCard($subject)
    {
        $code = $subject->getRequest()->getParam('giftcard_code');
        if (!empty($code)) {
            $couponCollection = $this->couponCollectionFactory->create()->addFieldToFilter('code', ['eq' => trim($code)]);
            //echo "<pre>"; print_r($couponCollection->getData()); echo "</pre>";
            //echo $couponCollection->getSelect();
            //var_dump($couponCollection->getSize());
            if ($couponCollection->getSize()) {
                $couponObj = $couponCollection->getFirstItem();
                return $couponObj;
//                echo "<pre>"; print_r($couponObj->getData()); echo "</pre>";
//                die('+++++');
            }
        }
    }*/

    /**
     * @param $code
     * @param $customerId
     * @param $store
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function redeemedGiftCardGetByCode($code, $customerId, $store): array
    {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('code', ['eq' => trim($code)]);
        $collection->addFieldToSelect(['coupon_id', 'rule_id', 'code']);

        $collection->getSelect()->joinLeft(
            ['gift_rule' => 'giftcard_rule'],
            'gift_rule.rule_id = main_table.rule_id', [
                'rule_id', 'to_date', 'discount_amount'
            ]
        );
        $collection->getSelect()
            ->joinLeft(['gcu' => 'giftcard_coupon_usage'], 'gcu.coupon_id = main_table.coupon_id')
            ->where('gcu.customer_id=?', $customerId);
        $balance = $this->_balanceFactory->create();
        $balance->setCustomerId($customerId)->loadByCustomer();
        if ($collection->getSize()) {
            $giftCardAccount = $collection->getFirstItem();
            return [
                'code' => $giftCardAccount->getCode(),
                'balance' => $this->moneyFormatter->formatAmountAsMoney(0, $store),
                'expiration_date' => $giftCardAccount->getToDate(),
                'store_credit' => $balance->getAmount()
            ];
        }
        return [
            'code' => $code,
            'balance' => $this->moneyFormatter->formatAmountAsMoney(0, $store),
            'expiration_date' => null,
            'store_credit' => $balance->getAmount()
        ];
    }

    /**
     * @param $code
     * @param $store
     * @return array|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomGiftCardAccountDetails($code, $store)
    {
        $collection = $this->couponCollectionFactory->create();
        $collection->addFieldToFilter('code', ['eq' => trim($code)]);
        $collection->addFieldToSelect(['coupon_id', 'rule_id', 'code', 'times_used']);

        $collection->getSelect()->joinLeft(
            ['gift_rule' => 'giftcard_rule'],
            'gift_rule.rule_id = main_table.rule_id', [
                'rule_id', 'to_date', 'discount_amount'
            ]
        );

        if ($collection->getSize()) {
            $coupon = $collection->getFirstItem();
            if ($coupon->getTimesUsed() > 0) {
                return [
                    'code' => $coupon->getCode(),
                    'balance' => $this->moneyFormatter->formatAmountAsMoney(0, $store),
                    'expiration_date' => $coupon->getToDate(),
                    'store_credit' => null
                ];
            } else {
                return [
                    'code' => $coupon->getCode(),
                    'balance' => $this->moneyFormatter->formatAmountAsMoney($coupon->getDiscountAmount(), $store),
                    'expiration_date' => $coupon->getToDate(),
                    'store_credit' => null
                ];
            }
        }
        return false;
    }

    /**
     * @return Logger
     */
    public function logger(): Logger
    {
        $writer = new Stream(BP . '/var/log/gift_card.log');
        $logger = new Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}
