<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Reward;

use Aheadworks\Raf\Api\AdvocateSummaryRepositoryInterface;
use Aheadworks\Raf\Api\Data\OrderInterface;
use Aheadworks\Raf\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

/**
 * Class Checker
 *
 * @package Aheadworks\Raf\Model\Advocate\Reward
 */
class Checker
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AdvocateSummaryRepositoryInterface
     */
    private $advocateSummaryRepository;

    /**
     * @param Config $config
     * @param AdvocateSummaryRepositoryInterface $advocateSummaryRepository
     */
    public function __construct(
        Config $config,
        AdvocateSummaryRepositoryInterface $advocateSummaryRepository
    ) {
        $this->config = $config;
        $this->advocateSummaryRepository = $advocateSummaryRepository;
    }

    /**
     * Check can give reward for friend purchase
     *
     * @param int $websiteId
     * @param OrderInterface $order
     * @return bool
     */
    public function canGiveRewardForFriendPurchase($websiteId, $order)
    {
        if (!$order->getAwRafIsAdvocateRewardReceived()
            && (int)$order->getAwRafIsFriendDiscount()
            && $order->getAwRafReferralLink()
            && ($this->config->getOrderStatusToGiveRewardToAdvocate($websiteId) == $order->getStatus()
                || Order::STATE_COMPLETE == $order->getState()
            )
        ) {
            try {
                $this->advocateSummaryRepository->getByReferralLink(
                    $order->getAwRafReferralLink(),
                    $websiteId
                );
                return true;
            } catch (NoSuchEntityException $e) {
            }
        }
        return false;
    }
}
