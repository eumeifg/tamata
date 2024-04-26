<?php
namespace Cminds\Coupon\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\SalesRule\Model\CouponFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Rule;
use Cminds\Coupon\Model\Error;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Cminds\Coupon\Helper\Data;
use Cminds\Coupon\Model\Count;
use Cminds\Coupon\Model\Log;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface as Logger;

class PreprocessApplyingCoupon implements ObserverInterface
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

    protected $context;
    protected $messageManager;
    protected $objectManager;
    protected $eventManager;
    protected $cart;
    protected $checkoutSession;
    protected $couponFactory;
    protected $request;
    protected $quoteRepository;
    protected $scopeConfigInterface;
    protected $coupon;
    protected $rule;
    protected $error;
    protected $dateTime;
    protected $session;
    protected $usage;
    protected $data;
    protected $count;
    protected $log;
    protected $escaper;
    protected $logger;

    public function __construct(
        Context $context,
        Cart $cart,
        CheckoutSession $checkoutSession,
        CouponFactory $couponFactory,
        QuoteRepository $quoteRepository,
        ScopeConfigInterface $scopeConfig,
        Coupon $couponModel,
        Rule $ruleModel,
        Error $error,
        DateTime $date,
        CustomerSession $customerSession,
        Usage $couponUsage,
        Data $cmindsHelper,
        Count $errorCount,
        Log $errorLog,
        Escaper $escaper,
        Logger $logger
    ) {
        $this->objectManager = $context->getObjectManager();
        $this->context = $context;
        $this->messageManager = $context->getMessageManager();
        $this->eventManager = $context->getEventManager();
        $this->cart = $cart;
        $this->couponFactory = $couponFactory;
        $this->checkoutSession = $checkoutSession;
        $this->request = $context->getRequest();
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfigInterface = $scopeConfig;
        $this->coupon = $couponModel;
        $this->rule = $ruleModel;
        $this->error = $error;
        $this->dateTime = $date;
        $this->session = $customerSession;
        $this->usage = $couponUsage;
        $this->data = $cmindsHelper;
        $this->count = $errorCount;
        $this->log = $errorLog;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfigInterface->getValue(self::ENABLE_COUPON_ERROR)) {
            $couponCode = $this->request->getParam('remove') == 1
                ? ''
                : trim($this->request->getParam('coupon_code'));

            $cartQuote = $this->cart->getQuote();

            $coupon = $this->coupon->load($couponCode, 'code');
            $rule = $this->rule->load($coupon->getRuleId());

            $oldCouponCode = $cartQuote->getCouponCode();

            $codeLength = strlen($couponCode);
            if (!$codeLength && $oldCouponCode === "") {
                return $this;
            }

            try {
                if ($codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH) {
                    $isCodeLengthValid = true;
                } else {
                    $isCodeLengthValid = false;
                }

                $customerSession = $this->session;
                $escaper = $this->escaper;

                if ($codeLength) {
                    if ($couponCode !== $cartQuote->getCouponCode()) {
                        if ($isCodeLengthValid) {
                            $coupon = $this->couponFactory->create();
                            $coupon->load($couponCode, 'code');
                            $existsError = false;

                            $errorCount = $this->count->load($coupon->getId(), 'coupon_id');

                            if ($coupon->getId() && !$errorCount->getId()) {
                                $errorCount->setCouponId($coupon->getId())
                                    ->setRuleId($coupon->getRuleId())
                                    ->save();
                            }


                            if (!$coupon->getId()) {
                                $this->messageManager->addError(
                                    __(
                                        sprintf($this->data->getErrorMessage(self::COUPON_NOT_EXIST, $rule), $couponCode),
                                        $escaper->escapeHtml($couponCode)
                                    )
                                );

                                $existsError = true;
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

                                    $this->messageManager->addError(
                                        __(
                                            sprintf($this->data->getErrorMessage(self::COUPON_NOT_EXIST, $rule), $couponCode),
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );

                                    $existsError = true;
                                }
                            }

                            if (!$existsError) {
                                $ruleExpiration = $rule->getToDate();
                                if ($coupon->getExpirationDate() != null || $ruleExpiration) {
                                    $expirationDate = $coupon->getExpirationDate() ?? $ruleExpiration;
                                    $now = $this->dateTime->gmtDate("Y-m-d 00:00:00");

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

                                        $this->messageManager->addError(
                                            __(
                                                sprintf($this->data->getErrorMessage(self::COUPON_EXPIRED, $rule), $couponCode),
                                                $escaper->escapeHtml($couponCode)
                                            )
                                        );
                                        $existsError = true;
                                    }
                                }
                            }

                            if (!$existsError
                                && $coupon->getUsageLimit()
                                && $coupon->getTimesUsed() + 1 > $coupon->getUsageLimit()
                            ) {
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

                                $this->messageManager->addError(
                                    __(
                                        sprintf($this->data->getErrorMessage(self::COUPON_USED_MULTIPLE, $rule), $couponCode),
                                        $escaper->escapeHtml($couponCode)
                                    )
                                );
                                $existsError = true;
                            }

                            if (!$existsError
                                && $customerSession->getCustomerGroupId()
                                && $coupon->getUsagePerCustomer()
                            ) {
                                $couponUsage = new DataObject();
                                $customerId = $customerSession->getCustomerId();
                                $this->usage->loadByCustomerCoupon($couponUsage, $customerId, $coupon->getId());

                                if ($couponUsage->getCouponId()
                                    && $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                                ) {
                                    $log = $this->log;
                                    $log->setDatetime(date('Y-m-d H:i:s'))
                                        ->setCouponId($coupon->getId())
                                        ->setErrorType(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP)
                                        ->save();

                                    $errorCount
                                        ->setCouponUsedMultipleCustomerGroup($errorCount->getCouponUsedMultipleCustomerGroup() + 1)
                                        ->setLastOccured(date('Y-m-d H:i:s'))
                                        ->setCouponId($coupon->getId())
                                        ->setRuleId($coupon->getRuleId())
                                        ->save();

                                    $this->messageManager->addError(
                                        __(
                                            sprintf($this->data->getErrorMessage(self::COUPON_USED_MULTIPLE_CUSTOMER_GROUP, $rule), $couponCode),
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );
                                    $existsError = true;
                                }
                            }

                            if (!$existsError) {
                                if (!in_array($customerSession->getCustomerGroupId(), $rule->getCustomerGroupIds())) {
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

                                    $this->messageManager->addError(
                                        __(
                                            sprintf($this->data->getErrorMessage(self::CUSTOMER_NOT_BELONG_GROUP, $rule), $couponCode),
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );
                                    $existsError = true;
                                }
                            }

                            if (!$existsError) {
                                $matchConditions = $this->data->hasProductsMatchConditions(
                                    $cartQuote,
                                    $rule->getConditions(),
                                    $couponCode
                                );
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

                                    $this->messageManager->addError(
                                        __(
                                            sprintf($this->data->getErrorMessage(self::COUPON_NOT_APPLY_RULE, $rule), $couponCode),
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );
                                    $existsError = true;
                                }
                            }

                            if (!$existsError) {
                                $matchConditions = $this->data->matchProducts($rule, $cartQuote, $couponCode);

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

                                    $this->messageManager->addError(
                                        __(
                                            sprintf($this->data->getErrorMessage(self::COUPON_NOT_APPLY_RULE, $rule), $couponCode),
                                            $escaper->escapeHtml($couponCode)
                                        )
                                    );

                                }
                            }
                        } else {
                            $this->messageManager->addError(
                                __(
                                    sprintf($this->data->getErrorMessage(self::COUPON_OTHER_MESSAGES, $rule), $couponCode),
                                    $escaper->escapeHtml($couponCode)
                                )
                            );

                            $existsError = true;
                        }
                    }

                    if (isset($existsError) && $existsError) {
                        $observer->getRequest()->setParams(['coupon_code'=> '']);
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We cannot apply the coupon code.'));
                $this->logger->critical($e);
            }
        }
    }
}
