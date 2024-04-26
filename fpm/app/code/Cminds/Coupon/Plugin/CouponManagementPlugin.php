<?php

namespace Cminds\Coupon\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject;
use Magento\Quote\Api\CartRepositoryInterface;
use Cminds\Coupon\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\SalesRule\Model\CouponFactory;
use Cminds\Coupon\Model\Log;
use Cminds\Coupon\Model\Count;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Coupon management object.
 */
class CouponManagementPlugin
{

    const ENABLE_COUPON_ERROR = 'coupon_error/general/enable_coupon_error';
    const COUPON_NOT_EXIST = 'coupon_not_exist';
    const COUPON_NOT_APPLY_RULE = 'coupon_not_apply_rule';
    const COUPON_EXPIRED = 'coupon_expired';
    const CUSTOMER_NOT_BELONG_GROUP = 'customer_not_belong_group';
    const COUPON_USED_MULTIPLE = 'coupon_used_multiple';
    const COUPON_USED_MULTIPLE_CUSTOMER_GROUP = 'coupon_used_multiple_customer_group';
    const COUPON_OTHER_MESSAGES = 'coupon_other_messages';
    const TYPE_REDIRECT = 'Magento\Framework\Controller\Result\Redirect';

    protected $data;
    protected $cart;
    protected $coupon;
    protected $rule;
    protected $scopeConfigInterface;
    protected $session;
    protected $couponFactory;
    protected $log;
    protected $count;
    protected $dateTime;
    protected $quoteRepository;
    protected $couponUsage;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Data $cmindsHelper,
        Cart $cart,
        Coupon $couponModel,
        Rule $ruleModel,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        CouponFactory $couponFactory,
        Log $errorLog,
        Count $errorCount,
        Usage $couponUsage,
        DateTime $date,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->data = $cmindsHelper;
        $this->cart = $cart;
        $this->coupon = $couponModel;
        $this->rule = $ruleModel;
        $this->scopeConfigInterface = $scopeConfig;
        $this->session = $customerSession;
        $this->couponFactory = $couponFactory;
        $this->log = $errorLog;
        $this->count = $errorCount;
        $this->dateTime = $date;
        $this->usage = $couponUsage;
        $this->userContext = $userContext;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function aroundSet(\Magento\Quote\Model\CouponManagement $subject, \Closure $closure, $cartId, $couponCode)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $quote->getShippingAddress()->setCollectShippingRates(true);

        $cartQuote = $this->cart->getQuote();//returns the cart  object

        $coupon = $this->coupon->load($couponCode, 'code');
        $rule = $this->rule->load($coupon->getRuleId());

        $codeLength = strlen($couponCode);
        try {
            $quote->setCouponCode($couponCode);
            $this->quoteRepository->save($quote->collectTotals());
            if ($this->scopeConfigInterface->getValue(self::ENABLE_COUPON_ERROR)) {
                $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;// true or false is code length valid
               
                $customerSession = $this->session;

                if($customerSession->getCustomerId() === NULL){
                    $customerId = $this->userContext->getUserId();
                    $authCustomer = $this->customerFactory->create()->load($customerId);
                    $customerGroupId = $authCustomer->getGroupId();
                }else{
                    $customerId = $customerSession->getCustomerId();
                    $customerGroupId = $customerSession->getCustomerGroupId();
                }

        
                 
                if ($isCodeLengthValid) {
                    $coupon = $this->couponFactory->create();
                    $coupon->load($couponCode, 'code');
                    $existsError = false;

                    $errorCount = $this->count->load($coupon->getId(), 'coupon_id');

                    if ($coupon->getId() && !$errorCount->getId()) {
                        $errorCount
                            ->setCouponId($coupon->getId())
                            ->setRuleId($coupon->getRuleId())
                            ->save();
                    }

                    if (!$coupon->getId()) {
                        $existsError = true;
                        throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_NOT_EXIST, $rule), $couponCode)));
                    }
                    if (!$existsError) {
                        if (!$rule->getIsActive()) {

                            $log = $this->log;
                            $log->setDatetime(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setErrorType(self::COUPON_NOT_EXIST)
                                ->save();

                            $errorCount->setCouponExpired($errorCount->getCouponExpired() + 1)
                                ->setLastOccured(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setRuleId($coupon->getRuleId())
                                ->save();

                            $existsError = true;

                            throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_NOT_EXIST, $rule), $couponCode)));
                        }
                    }
                    if (!$existsError) {
                        $ruleExpiration = $rule->getToDate();
                        if ($coupon->getExpirationDate() != null || $ruleExpiration) {
                            $expirationDate = $coupon->getExpirationDate() ?? $ruleExpiration;
                            $now = $this->dateTime->gmtDate();

                            if ($now > $expirationDate) {

                                $log = $this->log;
                                $log->setDatetime(date('Y-m-d H:i:s'))
                                    ->setCouponId($coupon->getId())
                                    ->setErrorType(self::COUPON_EXPIRED)
                                    ->save();

                                $errorCount->setCouponExpired($errorCount->getCouponExpired() + 1)
                                    ->setLastOccured(date('Y-m-d H:i:s'))
                                    ->setCouponId($coupon->getId())
                                    ->setRuleId($coupon->getRuleId())
                                    ->save();


                                $existsError = true;

                                throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_EXPIRED, $rule), $couponCode)));
                            }
                        }
                    }
                    if (!$existsError && $coupon->getUsageLimit() && $coupon->getTimesUsed() + 1 > $coupon->getUsageLimit()) {

                        $log = $this->log;
                        $log->setDatetime(date('Y-m-d H:i:s'))
                            ->setCouponId($coupon->getId())
                            ->setErrorType(self::COUPON_USED_MULTIPLE)
                            ->save();

                        $errorCount->setCouponUsedMultiple($errorCount->getCouponUsedMultiple() + 1)
                            ->setLastOccured(date('Y-m-d H:i:s'))
                            ->setCouponId($coupon->getId())
                            ->setRuleId($coupon->getRuleId())
                            ->save();


                        $existsError = true;
                        throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_USED_MULTIPLE, $rule), $couponCode)));
                    }

                    if (!$existsError && $customerGroupId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = new DataObject();
                        $customerId = $customerId;
                        $this->usage->loadByCustomerCoupon($couponUsage, $customerId, $coupon->getId());

                        if ($couponUsage->getCouponId() && $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()) {

                            $log = $this->log;
                            $log->setDatetime(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setErrorType(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP)
                                ->save();

                            $errorCount->setCouponUsedMultipleCustomerGroup($errorCount->getCouponUsedMultipleCustomerGroup() + 1)
                                ->setLastOccured(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setRuleId($coupon->getRuleId())
                                ->save();

                            $existsError = true;
                            throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP, $rule), $couponCode)));
                        }
                    }

                    if (!$existsError) {
                        if (!in_array($customerGroupId, $rule->getCustomerGroupIds())) {

                            $log = $this->log;
                            $log->setDatetime(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setErrorType(self::CUSTOMER_NOT_BELONG_GROUP)
                                ->save();

                            $errorCount->setCustomerNotBelongGroup($errorCount->getCustomerNotBelongGroup() + 1)
                                ->setLastOccured(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setRuleId($coupon->getRuleId())
                                ->save();

                            $existsError = true;
                            throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::CUSTOMER_NOT_BELONG_GROUP, $rule), $couponCode)));
                        }
                    }
                    if (!$existsError) {
                        $matchConditions = $this->data->hasProductsMatchConditions($quote, $rule->getConditions(), $couponCode);
                        if (!$matchConditions) {

                            $log = $this->log;
                            $log->setDatetime(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setErrorType(self::COUPON_NOT_APPLY_RULE)
                                ->save();

                            $errorCount->setCouponNotApplyRule($errorCount->getCouponNotApplyRule() + 1)
                                ->setLastOccured(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setRuleId($coupon->getRuleId())
                                ->save();


                            $existsError = true;
                            throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_NOT_APPLY_RULE, $rule), $couponCode)));
                        }
                    }
                    if (!$existsError) {
                        $matchConditions = $this->data->matchProducts($rule, $quote, $couponCode);

                        if (!$matchConditions) {

                            $log = $this->log;
                            $log->setDatetime(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setErrorType(self::COUPON_NOT_APPLY_RULE)
                                ->save();

                            $errorCount->setCouponNotApplyRule($errorCount->getCouponNotApplyRule() + 1)
                                ->setLastOccured(date('Y-m-d H:i:s'))
                                ->setCouponId($coupon->getId())
                                ->setRuleId($coupon->getRuleId())
                                ->save();

                            $existsError = true;
                            throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_NOT_APPLY_RULE, $rule), $couponCode)));
                        }
                    }
                    $errorCount->save();
                } else {
                    throw new NoSuchEntityException(__(sprintf($this->data->getErrorMessage(self::COUPON_OTHER_MESSAGES, $rule), $couponCode)));
                }
            }
        } catch (\Exception $e) {
            if ($this->scopeConfigInterface->getValue(self::ENABLE_COUPON_ERROR)) {
                throw new CouldNotSaveException(__($e->getMessage()));
            } else {
                throw new CouldNotSaveException(__('Could not apply coupon code'));
            }

        }
        if ($quote->getCouponCode() != $couponCode) {
            throw new NoSuchEntityException(__('Coupon code is not valid'));


        }
        return true;
    }
}
