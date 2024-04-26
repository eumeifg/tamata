<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Ktpl\OrderComment\Api\GuestOrderCommentManagementInterface;
use Ktpl\OrderComment\Api\OrderCommentManagementInterface;
use Ktpl\OrderComment\Api\Data\OrderCommentInterface;

class GuestOrderCommentManagement implements GuestOrderCommentManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var OrderCommentManagementInterface
     */
    protected $orderCommentManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderCommentManagementInterface $orderCommentManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderCommentManagementInterface $orderCommentManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderCommentManagement = $orderCommentManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderComment(
        $cartId,
        OrderCommentInterface $orderComment
    ){
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');

        return $this->orderCommentManagement->saveOrderComment(
            $quoteIdMask->getQuoteId(),
            $orderComment
        );
    }
}
