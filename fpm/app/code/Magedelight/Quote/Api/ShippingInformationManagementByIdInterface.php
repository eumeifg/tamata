<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Quote\Api;

/**
 * Interface for managing customer shipping address information
 * @api
 * @since 100.0.2
 */
interface ShippingInformationManagementByIdInterface
{
    /**
     * @param int $cartId
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveAddressInformation(
        $cartId
    );
}
