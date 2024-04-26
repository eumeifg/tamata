<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Api;

/**
 * Interface for saving the checkout order comment
 * to the quote for logged in users
 * @api
 */
interface OrderCommentManagementInterface
{
    /**
     * @param int $cartId
     * @param \Ktpl\OrderComment\Api\Data\OrderCommentInterface $orderComment
     * @return null|string
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveOrderComment(
        $cartId,
        \Ktpl\OrderComment\Api\Data\OrderCommentInterface $orderComment
    );
}
