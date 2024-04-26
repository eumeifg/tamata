<?php
namespace Cminds\Coupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Cminds\Coupon\Model\Error as CmindsError;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AdminCouponErrorMessageFormSave implements ObserverInterface
{

    const ENABLE_COUPON_ERROR = 'coupon_error/general/enable_coupon_error';

    protected $request;
    protected $scopeConfigInterface;
    protected $cmindsError;
    protected $rule;
    protected $error;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        CmindsError $errorModel,
        Rule $ruleModel,
        CmindsError $cmindsErrorModel
    ) {
        $this->scopeConfigInterface = $scopeConfig;
        $this->request = $request;
        $this->error = $errorModel;
        $this->rule = $ruleModel;
        $this->cmindsError = $cmindsErrorModel;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->scopeConfigInterface->getValue(self::ENABLE_COUPON_ERROR)) {
            $request = $this->request->getParams();
            if (isset($request['rule_id']) && isset($request['coupon_not_apply_rule'])) {
                $cmindsErrorModel = $this->cmindsError;
                $rule = $cmindsErrorModel->load($request['rule_id'], 'rule_id');
                $rule->setRuleId($request['rule_id']);
                $rule->setCouponId('1');
                $rule->setCouponNotApplyRule($request['coupon_not_apply_rule']);
                $rule->setCouponExpired($request['coupon_expired']);
                $rule->setCustomerNotBelongGroup($request['customer_not_belong_group']);
                $rule->setCouponUsedMultiple($request['coupon_used_multiple']);
                $rule->setCouponUsedMultipleCustomerGroup($request['coupon_used_multiple_customer_group']);
                $rule->setCouponOtherMessages($request['coupon_other_messages']);
                $rule->save();
            }
        }
    }
}