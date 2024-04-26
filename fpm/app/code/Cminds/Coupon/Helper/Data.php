<?php

namespace Cminds\Coupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Cminds\Coupon\Model\Error;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const ENABLE_COUPON_ERROR = 'coupon_error/general/enable_coupon_error';
    const COUPON_NOT_EXIST = 'coupon_error/general/coupon_not_exist';
    const COUPON_NOT_APPLY_RULE = 'coupon_error/general/coupon_not_apply_rule';
    const COUPON_EXPIRED = 'coupon_error/general/coupon_expired';
    const CUSTOMER_NOT_BELONG_GROUP = 'coupon_error/general/customer_not_belong_group';
    const COUPON_USED_MULTIPLE = 'coupon_error/general/coupon_used_multiple';
    const COUPON_USED_MULTIPLE_CUSTOMER_GROUP = 'coupon_error/general/coupon_used_multiple_customer_group';
    const COUPON_OTHER_MESSAGES = 'coupon_error/general/coupon_other_messages';

    protected $escaper;
    protected $messageManager;
    protected $session;
    protected $error;
    private $storeManager;


    public function __construct(
        Context $context,
        ActionContext $contextObject,
        Session $backendSession,
        Error $error,
        Escaper $escaper,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->messageManager = $contextObject->getMessageManager();
        $this->session = $backendSession;
        $this->error = $error;
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
    }

    public function getErrorMessage($errorType, $rule)
    {
        $defaultMessage = 'Coupon code "%1" is not valid.';
        if ($errorType == '') {
            return $this->escaper->escapeHtml($defaultMessage);
        }
        $storeId = $this->storeManager->getStore()->getId();

        $messageFromConfig = $this->scopeConfig->getValue(
            'coupon_error/general/' . $errorType,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($rule && $rule->getId()) {
            $ruleId = $rule->getId();
            $ruleError = $this->error->load($ruleId, 'rule_id');
            $ruleErrorMessage = $ruleError->getData($errorType);
            if ($ruleErrorMessage && $messageFromConfig) {
                return $ruleErrorMessage;
            } elseif ($messageFromConfig && !$ruleErrorMessage) {
                return $messageFromConfig;
            } elseif (!$messageFromConfig && $ruleErrorMessage) {
                return $ruleErrorMessage;
            } elseif (!$messageFromConfig && !$ruleErrorMessage) {
                return $defaultMessage;
            }
        } else {
            if ($messageFromConfig) {
                return $messageFromConfig;
            } else {
                return $defaultMessage;
            }
        }
    }

    /**
     * Get Quote Shipping Address or Billing Address if the Quote is virtual.
     *
     * @param Quote $quote
     *
     * @return Quote\Address|null
     */
    public function getQuoteAddress(Quote $quote)
    {
        $isVirtual = $quote->isVirtual();
        if ($isVirtual) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        if (!$address) {
            return null;
        }

        return $address;
    }

    /**
     * Set Quote Address total quantity. We do this because there is a bug with this extension.
     *
     * Let's consider we have the rule, which is bind with cart items quantity.
     * When we check the matched rules in our extension, the magento core code takes the quote address object and checks
     * for the "total quantity" (totalQty) property.
     * But when this extension makes a call to check the rules, the quote address
     * doesn't have information about cart total items quantity. This method calculates cart total items quantity, saves
     * it into the quote address object and returns this quote address object.
     *
     * @param Quote $quote
     *
     * @return Quote\Address|null
     */
    public function setQuoteAddressTotalQty(Quote $quote)
    {
        $address = $this->getQuoteAddress($quote);
        if (!$address) {
            return null;
        }

        $itemsQty = (int)$quote->getItemsQty();
        $address->setTotalQty($itemsQty);

        return $address;
    }

    public function hasProductsMatchConditions(
        Quote $quote,
        $ruleConditions,
        $couponCode
    ) {
        $all = $ruleConditions->getAggregator() === 'all';
        $true = true;
        $found = false;
        $errorMsg = $this->scopeConfig->getValue(self::COUPON_NOT_APPLY_RULE);
        $message = __($errorMsg, $this->escaper->escapeHtml($couponCode));

        $origAddress = $this->getQuoteAddress($quote);
        $address = $this->setQuoteAddressTotalQty($quote);
        if (!$address) {
            return false;
        }
        $validataResultsVocabulary = array();
        foreach ($quote->getAllItems() as $item) {
            $found = $all;
            foreach ($ruleConditions->getConditions() as $key => $cond) {
                //set total item quantity for shipping or billing address, in order to check quantity cart price rules.
                $item->setShippingAddress($address);
                if( $cond instanceof Found ){
                    if( !isset( $validataResultsVocabulary[$key] ) )
                        $validated = $validataResultsVocabulary[$key] = $cond->validate($quote);
                    else
                        $validated = $validataResultsVocabulary[$key];
                }
                else
                    $validated = $cond->validate($item);
                if (!$validated) {
                    $errors[$item->getItemId()] = $message;
                }

                //set unmodified address back
                //because the total quantity will be recalculated in the native magento code again.
                $item->setShippingAddress($origAddress);

                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }

        if ($found && $true) {
            return true;
        } elseif (!$found && !$true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if the quote items match coupon items conditions.
     *
     * @param Rule $rule
     * @param Quote $cart
     * @param string $couponCode
     *
     * @return bool
     */
    public function matchProducts($rule, $cart, $couponCode)
    {
        $errorMsg = $this->scopeConfig->getValue(self::COUPON_NOT_APPLY_RULE);
        $message = __($errorMsg, $this->escaper->escapeHtml($couponCode));
        $errors = array();

        foreach ($cart->getAllItems() as $item) {
            $conditions = $rule
                ->getActions()
                ->getConditions();
            foreach ($conditions as $cond) {
                $validated = $cond->validate($item);
                if (!$validated) {
                    $errors[$item->getItemId()] = $message;
                }
            }
        }

        return true;
    }

    public function isCouponErrorModuleEnabled()
    {
        if ($this->scopeConfig->getValue(self::ENABLE_COUPON_ERROR)) {
            return true;
        }
        return false;
    }
}
