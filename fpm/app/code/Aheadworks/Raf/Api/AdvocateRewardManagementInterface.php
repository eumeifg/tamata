<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface AdvocateRewardManagementInterface
 *
 * @package Aheadworks\Raf\Api
 */
interface AdvocateRewardManagementInterface
{
    /**
     * Give reward for friend purchase
     *
     * @param int $websiteId
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function giveRewardForFriendPurchase($websiteId, $order);
}
