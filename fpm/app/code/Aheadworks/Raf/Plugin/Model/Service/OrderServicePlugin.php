<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Service;

use Aheadworks\Raf\Api\AdvocateManagementInterface;
use Aheadworks\Raf\Api\TransactionHoldingPeriodManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Service\OrderService;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Class OrderServicePlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\Service
 */
class OrderServicePlugin
{
    /**
     * @var AdvocateManagementInterface
     */
    private $advocateManagement;

    /**
     * @var TransactionHoldingPeriodManagementInterface
     */
    private $transactionHoldingPeriodService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AdvocateManagementInterface $advocateManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param TransactionHoldingPeriodManagementInterface $transactionHoldingPeriodService
     */
    public function __construct(
        AdvocateManagementInterface $advocateManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        TransactionHoldingPeriodManagementInterface $transactionHoldingPeriodService
    ) {
        $this->advocateManagement = $advocateManagement;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->transactionHoldingPeriodService = $transactionHoldingPeriodService;
    }

    /**
     * Refund RAF discount for canceled order
     *
     * @param OrderService $subject
     * @param \Closure $proceed
     * @param int $orderId
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCancel($subject, \Closure $proceed, $orderId)
    {
        $result = $proceed($orderId);
        if ($result) {
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);
            $customerId = $order->getCustomerId();
            $websiteId = $order->getStore()->getWebsiteId();
            try {
                $this->advocateManagement->refundReferralDiscountForCanceledOrder($customerId, $websiteId, $order);
                $this->transactionHoldingPeriodService->cancelTransactionForCanceledOrder($order);
            } catch (LocalizedException $e) {
                $this->logger->error($e);
            }
        }
        return $result;
    }

    /**
     * Spend customer RAF discount on checkout after place order
     *
     * @param OrderService $subject
     * @param Order|OrderInterface $result
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderService $subject, OrderInterface $result)
    {
        $customerId = $result->getCustomerId();
        $websiteId = $result->getStore()->getWebsiteId();
        try {
            $this->advocateManagement->spendDiscountOnCheckout($customerId, $websiteId, $result);
        } catch (LocalizedException $e) {
            $this->logger->error($e);
        }

        return $result;
    }
}
