<?php

namespace CAT\ReferralProgram\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class CouponManagementPlugin
 * @package CAT\ReferralProgram\Plugin
 */
class CouponManagementPlugin
{

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $couponManagement
     * @param $cartId
     * @param $couponCode
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSet(\Magento\Quote\Model\CouponManagement $couponManagement, $cartId, $couponCode) {
        $quote = $this->quoteRepository->get($cartId);
        if (!empty($quote->getCustomer()->getId())) {
            $customer = $this->customerRepository->getById($quote->getCustomer()->getId());
            if (!empty($customer->getCustomAttribute('customer_referral_code'))) {
                $referralCode = $customer->getCustomAttribute('customer_referral_code')->getValue();
                if ($couponCode == $referralCode) {
                    throw new NoSuchEntityException(__('Referral code '.$couponCode.' belongs to you. You can\'t apply this coupon.'));
                }
            }
        }
    }
}
