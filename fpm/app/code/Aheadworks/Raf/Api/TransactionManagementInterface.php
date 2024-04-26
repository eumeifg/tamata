<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api;

/**
 * Interface TransactionManagementInterface
 * @api
 */
interface TransactionManagementInterface
{
    /**
     * Create transaction
     *
     * @param int $customerId
     * @param int $websiteId
     * @param string $action
     * @param float $amount
     * @param string $amountType
     * @param int|null $createdBy
     * @param string|null $adminComment
     * @param \Magento\Sales\Api\Data\OrderInterface[]|\Magento\Sales\Api\Data\CreditmemoInterface[]
     * |\Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Api\Data\CreditmemoInterface|null $entities
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Aheadworks\Raf\Api\Data\TransactionInterface
     */
    public function createTransaction(
        $customerId,
        $websiteId,
        $action,
        $amount,
        $amountType,
        $createdBy,
        $adminComment,
        $entities
    );
}
