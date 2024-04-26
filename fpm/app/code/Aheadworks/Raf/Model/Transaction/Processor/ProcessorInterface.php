<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Processor;

use Aheadworks\Raf\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface ProcessorInterface
 *
 * @package Aheadworks\Raf\Model\Transaction\Processor
 */
interface ProcessorInterface
{
    /**
     * Transaction processor
     *
     * @param int $customerId
     * @param int $websiteId
     * @param string $action
     * @param float $amount
     * @param string $amountType
     * @param $createdBy|null
     * @param $adminComment|null
     * @param OrderInterface[]|CreditmemoInterface[]|null $entities
     * @return TransactionInterface
     */
    public function process(
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
