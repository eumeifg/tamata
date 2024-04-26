<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Ktpl\OrderComment\Api\OrderCommentManagementInterface;
use Ktpl\OrderComment\Api\Data\OrderCommentInterface;
use Ktpl\OrderComment\Model\Data\OrderComment;

class OrderCommentManagement implements OrderCommentManagementInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderComment(
        $cartId,
        OrderCommentInterface $orderComment
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }

        $comment = $orderComment->getOrderComment();

        try {
            $quote->setData(OrderCommentInterface::COLUMN_NAME, strip_tags($comment));
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The order comment could not be saved')
            );
        }

        return $comment;
    }
}
