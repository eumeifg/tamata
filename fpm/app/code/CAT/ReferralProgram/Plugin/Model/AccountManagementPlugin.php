<?php

namespace CAT\ReferralProgram\Plugin\Model;

use CAT\ReferralProgram\Helper\Data as ReferralHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class AccountManagementPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var ReferralHelper
     */
    protected $referralHelper;

    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ReferralHelper $referralHelper
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->referralHelper = $referralHelper;
    }
    public function afterCreateAccountWithPasswordHash(
        \Magento\Customer\Model\AccountManagement $subject,
        $result,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        $moduleEnabled = $this->referralHelper->isCustomerReferralModuleEnabled();
        if ($moduleEnabled) {
            $couponCodeArray = $this->referralHelper->generateCouponCode();
            if (!empty($couponCodeArray)) {
                $result->setCustomAttribute('customer_referral_code', $couponCodeArray[0]);
                $this->_customerRepositoryInterface->save($result);
            }
        }
        return $result;
    }
}
