<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Cron\Advocate;

use Aheadworks\Raf\Api\TransactionHoldingPeriodManagementInterface;
use Aheadworks\Raf\Cron\Management;
use Aheadworks\Raf\Model\Flag;
use Psr\Log\LoggerInterface;

/**
 * Class TransactionProcessor
 *
 * @package Aheadworks\Raf\Cron\Advocate
 */
class TransactionProcessor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Management
     */
    private $cronManagement;

    /**
     * @var TransactionHoldingPeriodManagementInterface
     */
    private $transactionHoldingPeriodManagement;

    /**
     * @param LoggerInterface $logger
     * @param Management $cronManagement
     * @param TransactionHoldingPeriodManagementInterface $transactionHoldingPeriodManagement
     */
    public function __construct(
        LoggerInterface $logger,
        Management $cronManagement,
        TransactionHoldingPeriodManagementInterface $transactionHoldingPeriodManagement
    ) {
        $this->logger = $logger;
        $this->cronManagement = $cronManagement;
        $this->transactionHoldingPeriodManagement = $transactionHoldingPeriodManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->cronManagement->isLocked(Flag::AW_RAF_ADVOCATE_TRANSACTION_PROCESSOR_LAST_EXEC_TIME)) {
            try {
                $this->transactionHoldingPeriodManagement->processExpiredTransactions();
            } catch (\LogicException $e) {
                $this->logger->error($e);
            }
            $this->cronManagement->setFlagData(Flag::AW_RAF_ADVOCATE_TRANSACTION_PROCESSOR_LAST_EXEC_TIME);
        }
    }
}
