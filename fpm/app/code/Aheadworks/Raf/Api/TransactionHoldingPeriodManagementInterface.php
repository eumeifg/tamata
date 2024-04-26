<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface TransactionHoldingPeriodManagementInterface
 *
 * @package Aheadworks\Raf\Api
 */
interface TransactionHoldingPeriodManagementInterface
{
    /**
     * Process transactions with expired holding period
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processExpiredTransactions();

    /**
     * Cancel transaction on order cancellation
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cancelTransactionForCanceledOrder($order);
}
