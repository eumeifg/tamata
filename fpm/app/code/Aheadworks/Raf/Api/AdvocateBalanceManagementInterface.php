<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

use Aheadworks\Raf\Api\Data\TransactionInterface;

/**
 * Interface AdvocateBalanceManagementInterface
 * @api
 */
interface AdvocateBalanceManagementInterface
{
    /**
     * Check balance of an advocate
     *
     * @param int $customerId
     * @param int $websiteId
     * @return bool
     */
    public function checkBalance($customerId, $websiteId);

    /**
     * Get balance of an advocate
     *
     * @param int $customerId
     * @param int $websiteId
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBalance($customerId, $websiteId);

    /**
     * Get balance discount type
     *
     * @param int $customerId
     * @param int $websiteId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDiscountType($customerId, $websiteId);

    /**
     * Update balance
     *
     * @param int $customerId
     * @param int $websiteId
     * @param TransactionInterface $transaction
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateBalance($customerId, $websiteId, $transaction);
}
