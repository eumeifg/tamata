<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Metadata\Friend;

use Aheadworks\Raf\Api\Data\FriendMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Builder
 *
 * @package Aheadworks\Raf\Model\Metadata\Friend
 */
class Builder
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Build friend metadata
     *
     * @param Quote $quote
     * @return FriendMetadataInterface
     */
    public function build($quote)
    {
        $customerId = $quote->getCustomerId() ? : '';
        $customerEmail = $quote->getCustomerEmail() ? : '';
        $remoteIp = $quote->getRemoteIp() ? : '';

        $friendData = [
            FriendMetadataInterface::CUSTOMER_ID => $customerId,
            FriendMetadataInterface::CUSTOMER_EMAIL => $customerEmail,
            FriendMetadataInterface::CUSTOMER_IP => $remoteIp
        ];

        return $this->objectManager->create(FriendMetadataInterface::class, ['data' => $friendData]);
    }
}
