<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\HoldingPeriod;

use Aheadworks\Raf\Api\Data\TransactionInterface;
use Aheadworks\Raf\Api\AdvocateBalanceManagementInterface;
use Aheadworks\Raf\Api\AdvocateSummaryRepositoryInterface;
use Aheadworks\Raf\Model\Advocate\Balance\Processor as BalanceProcessor;

/**
 * Class Balance
 *
 * @package Aheadworks\Raf\Model\Transaction\HoldingPeriod
 */
class Balance
{
    /**
     * @var AdvocateSummaryRepositoryInterface
     */
    private $advocateRepository;

    /**
     * @var AdvocateBalanceManagementInterface
     */
    private $advocateBalanceManagement;

    /**
     * @var BalanceProcessor
     */
    private $balanceProcessor;

    /**
     * @param AdvocateBalanceManagementInterface $advocateBalanceManagement
     * @param AdvocateSummaryRepositoryInterface $advocateRepository
     * @param BalanceProcessor $balanceProcessor
     */
    public function __construct(
        AdvocateBalanceManagementInterface $advocateBalanceManagement,
        AdvocateSummaryRepositoryInterface $advocateRepository,
        BalanceProcessor $balanceProcessor
    ) {
        $this->advocateBalanceManagement = $advocateBalanceManagement;
        $this->advocateRepository = $advocateRepository;
        $this->balanceProcessor = $balanceProcessor;
    }

    /**
     * Update balance for advocate using on hold transaction
     *
     * @param TransactionInterface $transaction
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function update($transaction)
    {
        $advocateSummary = $this->advocateRepository->get($transaction->getSummaryId());
        $this->advocateBalanceManagement->updateBalance(
            $advocateSummary->getCustomerId(),
            $advocateSummary->getWebsiteId(),
            $transaction
        );
        $this->balanceProcessor->process(
            $transaction,
            $advocateSummary,
            $transaction->getAmountType(),
            $transaction->getAmount()
        );
    }
}
