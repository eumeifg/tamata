<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Service;

use Aheadworks\Raf\Api\AdvocateExpirationManagementInterface;
use Aheadworks\Raf\Api\AdvocateSummaryRepositoryInterface;
use Aheadworks\Raf\Api\TransactionManagementInterface;
use Aheadworks\Raf\Model\Advocate\Notifier;
use Aheadworks\Raf\Model\Source\Customer\Advocate\Email\ReminderStatus;
use Aheadworks\Raf\Model\Source\Rule\AdvocateOffType;
use Aheadworks\Raf\Model\Source\Transaction\Action;
use Aheadworks\Raf\Model\Advocate\Expiration\Processor as ExpirationProcessor;
use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Aheadworks\Raf\Api\Data\TransactionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AdvocateExpirationService
 *
 * @package Aheadworks\Raf\Model\Service
 */
class AdvocateExpirationService implements AdvocateExpirationManagementInterface
{
    /**
     * @var AdvocateSummaryRepositoryInterface
     */
    private $advocateSummaryRepository;

    /**
     * @var TransactionManagementInterface
     */
    private $transactionManagement;

    /**
     * @var ExpirationProcessor
     */
    private $expirationProcessor;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @param AdvocateSummaryRepositoryInterface $advocateSummaryRepository
     * @param TransactionManagementInterface $transactionManagement
     * @param ExpirationProcessor $expirationProcessor
     * @param Notifier $notifier
     */
    public function __construct(
        AdvocateSummaryRepositoryInterface $advocateSummaryRepository,
        TransactionManagementInterface $transactionManagement,
        ExpirationProcessor $expirationProcessor,
        Notifier $notifier
    ) {
        $this->advocateSummaryRepository = $advocateSummaryRepository;
        $this->transactionManagement = $transactionManagement;
        $this->expirationProcessor = $expirationProcessor;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function expireBalance()
    {
        $advocatesSummary = $this->expirationProcessor->getAdvocatesBalanceToExpire();
        foreach ($advocatesSummary as $advocateSummary) {
            if ($advocateSummary->getCumulativeAmount() > 0) {
                $transaction = $this->createTransaction(
                    $advocateSummary,
                    AdvocateOffType::FIXED,
                    $advocateSummary->getCumulativeAmount()
                );
                $this->notifier->notifyAboutBalanceExpired($advocateSummary, $transaction);
            }
            if ($advocateSummary->getCumulativePercentAmount() > 0) {
                $transaction = $this->createTransaction(
                    $advocateSummary,
                    AdvocateOffType::PERCENT,
                    $advocateSummary->getCumulativePercentAmount()
                );
                $this->notifier->notifyAboutBalanceExpired($advocateSummary, $transaction);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendExpirationReminder()
    {
        $advocatesSummary = $this->expirationProcessor->getAdvocatesWhichBalanceExpires();
        foreach ($advocatesSummary as $advocateSummary) {
            $notified = $this->notifier->expirationReminder(
                $advocateSummary,
                $advocateSummary->getCumulativeAmount(),
                AdvocateOffType::FIXED
            );
            $reminderStatus = $notified ? ReminderStatus::SENT : ReminderStatus::FAILED;

            $advocateSummary->setReminderStatus($reminderStatus);
            $this->advocateSummaryRepository->save($advocateSummary);
        }
    }

    /**
     * Create transaction
     *
     * @param AdvocateSummaryInterface $advocateSummary
     * @param string $amountType
     * @param float $amount
     * @return TransactionInterface
     * @throws LocalizedException
     */
    private function createTransaction($advocateSummary, $amountType, $amount)
    {
        $transaction = $this->transactionManagement->createTransaction(
            $advocateSummary->getCustomerId(),
            $advocateSummary->getWebsiteId(),
            Action::EXPIRED,
            -$amount,
            $amountType,
            null,
            null,
            null
        );

        return $transaction;
    }
}
