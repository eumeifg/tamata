<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Friend;

use Aheadworks\Raf\Api\Data\FriendMetadataInterface;
use Aheadworks\Raf\Model\ResourceModel\Friend\Order as FriendOrderResource;

/**
 * Class Checker
 *
 * @package Aheadworks\Raf\Model\Friend
 */
class Checker
{
    /**
     * @var FriendOrderResource
     */
    private $friendOrderResource;

    /**
     * @param FriendOrderResource $friendOrderResource
     */
    public function __construct(FriendOrderResource $friendOrderResource)
    {
        $this->friendOrderResource = $friendOrderResource;
    }

    /**
     * Check if can apply discount
     *
     * @param FriendMetadataInterface $friendMetadata
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function canApplyDiscount($friendMetadata)
    {
        $numberOrders = $this->friendOrderResource->getNumberOfOrders($friendMetadata);

        return $numberOrders == 0;
    }
}
