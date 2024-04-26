<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Service;

use Aheadworks\Raf\Api\GuestQuoteManagementInterface;
use Aheadworks\Raf\Api\QuoteManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestQuoteService
 *
 * @package Aheadworks\Raf\Model\Service
 */
class GuestQuoteService implements GuestQuoteManagementInterface
{
    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @param QuoteManagementInterface $quoteManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        QuoteManagementInterface $quoteManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function updateReferralLink($maskedId, $referralLink)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedId, 'masked_id');
        return $this->quoteManagement->updateReferralLink($quoteIdMask->getQuoteId(), $referralLink);
    }
}
