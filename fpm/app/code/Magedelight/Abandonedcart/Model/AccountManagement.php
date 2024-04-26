<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Abandonedcart
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Abandonedcart\Model;

class AccountManagement
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->_objectManager = $objectManager;
    }

    /**
     * @return null || boolean
     */
    public function beforeIsEmailAvailable(
        \Magento\Customer\Model\AccountManagement $subject,
        $customerEmail,
        $websiteId = null
    ) {
        $cart = $this->_objectManager->get(\Magento\Checkout\Model\Cart::class);
        $shippingAddress = $cart->getQuote()->getShippingAddress();
        $billingAddress = $cart->getQuote()->getBillingAddress();

        try {
            $cart->getQuote()->setCustomerEmail($customerEmail);
            $cart->getQuote()->save();
            if ($shippingAddress) {
                $shippingAddress->setData('email', $customerEmail);
                $shippingAddress->save();
            }

            if ($billingAddress) {
                $billingAddress->setData('email', $customerEmail);
                $billingAddress->save();
            }

            return null;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $logger->info($e->getMessage());
        }

        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }
            $this->customerRepository->get($customerEmail, $websiteId);
            return false;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return true;
        }
    }
}
