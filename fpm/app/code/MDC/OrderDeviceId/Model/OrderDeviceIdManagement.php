<?php
/**
 * Copyright © MDC, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\OrderDeviceId\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use MDC\OrderDeviceId\Api\OrderDeviceIdManagementInterface;
use MDC\OrderDeviceId\Api\Data\OrderDeviceIdInterface;
use MDC\OrderDeviceId\Model\Data\OrderDeviceId;

class OrderDeviceIdManagement implements OrderDeviceIdManagementInterface
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
    public function saveOrderDeviceId(
        $cartId,
        OrderDeviceIdInterface $orderDeviceId
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }

        $deviceId = $orderDeviceId->getOrderDeviceId();

        try {
            $quote->setData(OrderDeviceIdInterface::COLUMN_NAME, strip_tags($deviceId));
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('The Device Id could not be saved')
            );
        }

        return $deviceId;
    }
}
