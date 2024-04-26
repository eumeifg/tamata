<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Transaction\Processor\Resolver;

use Aheadworks\Raf\Api\Data\TransactionEntityInterfaceFactory;
use Aheadworks\Raf\Api\Data\TransactionEntityInterface;
use Aheadworks\Raf\Model\Source\Transaction\EntityType;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class Entity
 *
 * @package Aheadworks\Raf\Model\Transaction\Processor\Resolver
 */
class Entity
{
    /**
     * @var TransactionEntityInterfaceFactory
     */
    private $transactionEntityFactory;

    /**
     * @param TransactionEntityInterfaceFactory $transactionEntityFactory
     */
    public function __construct(
        TransactionEntityInterfaceFactory $transactionEntityFactory
    ) {
        $this->transactionEntityFactory = $transactionEntityFactory;
    }

    /**
     * Resolve entity
     *
     * @param OrderInterface[]|CreditmemoInterface[]|OrderInterface|CreditmemoInterface $entities
     * @return TransactionEntityInterface[]
     */
    public function resolve($entities)
    {
        $transactionEntities = [];
        $entities = is_array($entities) ? $entities : [$entities];
        foreach ($entities as $entity) {
            $transactionEntity = false;
            if ($entity instanceof OrderInterface) {
                /** @var TransactionEntityInterface $transactionEntity */
                $transactionEntity = $this->transactionEntityFactory->create();
                $transactionEntity
                    ->setEntityId($entity->getEntityId())
                    ->setEntityLabel($entity->getIncrementId())
                    ->setEntityType(EntityType::ORDER_ID);
            } elseif ($entity instanceof CreditmemoInterface) {
                /** @var TransactionEntityInterface $transactionEntity */
                $transactionEntity = $this->transactionEntityFactory->create();
                $transactionEntity
                    ->setEntityId($entity->getEntityId())
                    ->setEntityLabel($entity->getIncrementId())
                    ->setEntityType(EntityType::CREDIT_MEMO_ID);
            }
            if ($transactionEntity) {
                $transactionEntities[] = $transactionEntity;
            }
        }

        return $transactionEntities;
    }
}
