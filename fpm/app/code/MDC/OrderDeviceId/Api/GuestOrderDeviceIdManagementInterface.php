<?php
/**
 * Copyright © Magedelight, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Api;

/**
 * Interface for saving the checkout order comment
 * to the quote for guest users
 * @api
 */
interface GuestOrderDeviceIdManagementInterface
{
    /**
     * @param string $cartId
     * @param \MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface $orderDeviceId
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveOrderDeviceId(
        $cartId,
        \MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface $orderDeviceId
    );
}
