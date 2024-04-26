<?php

namespace CAT\ReferralProgram\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\Service\CouponManagementService;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const IS_CUSTOMER_REFERRAL_ENABLE = 'cat_customer_referral/general/enable';
    const CUSTOMER_REFERRAL_RULE = 'cat_customer_referral/general/referral_rule';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CouponGenerationSpecInterfaceFactory|mixed
     */
    private $generationSpecFactory;

    /**
     * @var CouponManagementService
     */
    private $couponManagementService;

    /**
     * Data constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param CouponGenerationSpecInterfaceFactory|null $generationSpecFactory
     * @param CouponManagementService $couponManagementService
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        CouponGenerationSpecInterfaceFactory $generationSpecFactory = null,
        CouponManagementService $couponManagementService
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->generationSpecFactory = $generationSpecFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(
                CouponGenerationSpecInterfaceFactory::class
            );
        $this->couponManagementService = $couponManagementService;
    }

    public function isCustomerReferralModuleEnabled() {
        return $this->scopeConfig->getValue(self::IS_CUSTOMER_REFERRAL_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    public function getSelectedCouponRule() {
        return $this->scopeConfig->getValue(self::CUSTOMER_REFERRAL_RULE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|false
     */
    public function prepareRuleData() {
        if($this->getSelectedCouponRule()) {
            $couponRuleArray = [
                'rule_id' => $this->getSelectedCouponRule(),
                'qty' => 1,
                'length' => 12,
                'format' => 'alphanum',
                'dash' => 4
            ];
            $couponRuleArray['quantity'] = isset($couponRuleArray['qty']) ? $couponRuleArray['qty'] : null;
            return $couponRuleArray;
        }
        return false;
    }

    /**
     * @return false|string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateCouponCode() {
        if($data = $this->prepareRuleData()) {
            $couponSpec = $this->generationSpecFactory->create(['data' => $data]);
            return $this->couponManagementService->generate($couponSpec);
        }
        return false;
    }
}
