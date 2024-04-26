<?php

namespace CAT\ReferralProgram\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use CAT\ReferralProgram\Helper\Data as ReferralHelper;

class UpdateCustomerMeta implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepositoryInterface;

    /**
     * @var ReferralHelper
     */
    protected $referralHelper;

    /**
     * UpdateCustomerMeta constructor.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ReferralHelper $referralHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        ReferralHelper $referralHelper
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->referralHelper = $referralHelper;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(Observer $observer)
    {
        $moduleEnabled = $this->referralHelper->isCustomerReferralModuleEnabled();
        if ($moduleEnabled) {
            $couponCodeArray = $this->referralHelper->generateCouponCode();
            if (!empty($couponCodeArray)) {
                $customer = $observer->getEvent()->getCustomer();
                $customer->setCustomAttribute('customer_referral_code', $couponCodeArray[0]);
                $this->_customerRepositoryInterface->save($customer);
            }
        }
        return $this;
    }
}
