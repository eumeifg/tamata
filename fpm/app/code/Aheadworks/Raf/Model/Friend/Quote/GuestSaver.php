<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Friend\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\Session;

/**
 * Class GuestSaver
 *
 * @package Aheadworks\Raf\Model\Friend\Quote
 */
class GuestSaver
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param CheckoutSession $checkoutSession
     * @param Session $customerSession
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        Session $customerSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * Add customer email to guest quote
     *
     * @param string $customerEmail
     */
    public function addCustomerEmailToGuestQuote($customerEmail)
    {
        if (!$this->customerSession->isLoggedIn()) {
            try {
                $quote = $this->checkoutSession->getQuote();
                $quote->setCustomerEmail($customerEmail);
                $this->quoteRepository->save($quote);
            } catch (LocalizedException $e) {
            }
        }
    }
}
