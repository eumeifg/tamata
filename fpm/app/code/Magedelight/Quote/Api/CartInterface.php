<?php

namespace Magedelight\Quote\Api;

/**
 * Interface for CartItemRepositoryInterface
 */
interface CartInterface
{

    /**
     * Add/update the specified cart item.
     * @api
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified cart does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified item could not be saved to the cart.
     * @throws \Magento\Framework\Exception\InputException The specified item or cart is not valid.
     */
    public function save();
}
