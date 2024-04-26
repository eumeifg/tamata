<?php
/**
 * Copyright © MDC All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use MDC\OrderDeviceId\Api\GuestOrderDeviceIdManagementInterface;
use MDC\OrderDeviceId\Api\OrderDeviceIdManagementInterface;
use MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface;

class GuestOrderDeviceIdManagement implements GuestOrderDeviceIdManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var OrderDeviceIdManagementInterface
     */
    protected $orderDeviceIdManagement;

    /**
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param OrderDeviceIdManagementInterface $orderDeviceIdManagement
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        OrderDeviceIdManagementInterface $orderCommentManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->orderDeviceIdManagement = $orderDeviceIdManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderDeviceId(
        $cartId,
        OrderDeviceIdInterface $orderDeviceId
    ){
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');

        return $this->orderDeviceIdManagement->saveOrderDeviceId(
            $quoteIdMask->getQuoteId(),
            $orderDeviceId
        );
    }
}
