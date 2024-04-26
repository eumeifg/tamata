<?php

namespace CAT\ReferralProgram\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Helper\Session\CurrentCustomer;
use CAT\ReferralProgram\Helper\Data as ReferralHelper;

class ReferralDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var ReferralHelper
     */
    protected $referralHelper;

    /**
     * ReferralDetails constructor.
     * @param Template\Context $context
     * @param CurrentCustomer $currentCustomer
     * @param ReferralHelper $referralHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CurrentCustomer $currentCustomer,
        ReferralHelper $referralHelper,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->referralHelper = $referralHelper;
        parent::__construct($context, $data);
    }

    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function isReferralModuleEnable() {
        return $this->referralHelper->isCustomerReferralModuleEnabled();
    }

    public function getReferralCoupon() {
        $customer = $this->getCustomer();
        $customAttribute = $customer->getCustomAttribute('customer_referral_code');
        if (!empty($customAttribute)) {
            return $customer->getCustomAttribute('customer_referral_code')->getValue();
        }
        return;
    }
}
