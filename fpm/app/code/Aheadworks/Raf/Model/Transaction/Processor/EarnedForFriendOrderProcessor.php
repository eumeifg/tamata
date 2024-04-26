<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Processor;

use Aheadworks\Raf\Api\AdvocateSummaryRepositoryInterface;
use Aheadworks\Raf\Api\Data\TransactionInterfaceFactory;
use Aheadworks\Raf\Api\RuleManagementInterface;
use Aheadworks\Raf\Model\Transaction\Processor\Resolver\Entity;
use Aheadworks\Raf\Model\Transaction\Comment\Processor as CommentProcessor;
use Aheadworks\Raf\Model\Advocate\Balance\Processor as BalanceProcessor;
use Aheadworks\Raf\Model\Transaction\Processor\Resolver\HoldingPeriodExpiration;
use Aheadworks\Raf\Model\Source\Transaction\Status as TransactionStatus;

/**
 * Class EarnedForFriendOrderProcessor
 *
 * @package Aheadworks\Raf\Model\Transaction\Processor
 */
class EarnedForFriendOrderProcessor extends BaseProcessor implements ProcessorInterface
{
    /**
     * @var RuleManagementInterface
     */
    private $ruleManagement;

    /**
     * @param TransactionInterfaceFactory $transactionFactory
     * @param AdvocateSummaryRepositoryInterface $advocateSummaryRepository
     * @param Entity $entityResolver
     * @param CommentProcessor $commentProcessor
     * @param BalanceProcessor $balanceProcessor
     * @param RuleManagementInterface $ruleManagement
     * @param HoldingPeriodExpiration $holdingPeriodResolver
     */
    public function __construct(
        TransactionInterfaceFactory $transactionFactory,
        AdvocateSummaryRepositoryInterface $advocateSummaryRepository,
        Entity $entityResolver,
        CommentProcessor $commentProcessor,
        BalanceProcessor $balanceProcessor,
        RuleManagementInterface $ruleManagement,
        HoldingPeriodExpiration $holdingPeriodResolver
    ) {
        parent::__construct(
            $transactionFactory,
            $advocateSummaryRepository,
            $entityResolver,
            $commentProcessor,
            $balanceProcessor,
            $holdingPeriodResolver
        );
        $this->ruleManagement = $ruleManagement;
    }

    /**
     * {@inheritdoc}
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
    ) {
        $rule = $this->ruleManagement->getActiveRule($websiteId);
        $amount = $rule->getAdvocateOff();
        $amountType = $rule->getAdvocateOffType();

        $transaction = parent::process(
            $customerId,
            $websiteId,
            $action,
            $amount,
            $amountType,
            $createdBy,
            $adminComment,
            $entities
        );

        $expirationDate = $this->holdingPeriodResolver->resolveExpirationDate($websiteId);
        if ($expirationDate) {
            $advocateSummary = $this->advocateSummaryRepository->getByCustomerId($customerId, $websiteId);
            $transaction
                ->setStatus(TransactionStatus::PENDING)
                ->setHoldingPeriodExpiration($expirationDate)
                ->setBalanceAmount($advocateSummary->getCumulativeAmount())
                ->setPercentBalanceAmount($advocateSummary->getCumulativePercentAmount());
        }

        return $transaction;
    }
}
