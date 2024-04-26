<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Service;

use Aheadworks\Raf\Api\Data\TotalsInterface;
use Aheadworks\Raf\Api\QuoteManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Quote
 *
 * @package Aheadworks\Raf\Model\Service
 */
class QuoteService implements QuoteManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ruleFactory
     */
    private $ruleFactory;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        \Aheadworks\Raf\Model\RuleFactory $ruleFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function updateReferralLink($quoteId, $referralLink)
    {
        try {

            $ruleCollection = $this->ruleFactory->create();
            $rafRule = $ruleCollection->getCollection()->getFirstItem();

            if($rafRule->getStatus() == 1){
 
                /** @var Quote $quote */
                $quote = $this->quoteRepository->get($quoteId);
                $quote->setData(TotalsInterface::AW_RAF_REFERRAL_LINK, $referralLink);
                $this->quoteRepository->save($quote);
                return true;
            }
           
        } catch (\Exception $e) {
        }

        return false;
    }
}
