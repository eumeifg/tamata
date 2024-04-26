<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\ResourceModel;

use Aheadworks\Raf\Api\Data\TransactionInterface;
use Aheadworks\Raf\Model\Source\Transaction\EntityType;
use Aheadworks\Raf\Model\Source\Transaction\Action;
use Aheadworks\Raf\Model\Source\Transaction\Status;

/**
 * Class Transaction
 * @package Aheadworks\Raf\Model\ResourceModel
 */
class Transaction extends AbstractResourceModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_raf_transaction', 'id');
    }

    /**
     * Get transaction ID created for friend order
     *
     * @param int $orderId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTransactionIdCreatedForFriendOrder($orderId)
    {
        $connection = $this->getConnection();
        $joinCondition = [
            'id = entity.transaction_id',
            $connection->quoteInto('entity.entity_type = ?', EntityType::ORDER_ID),
            'entity.entity_id = ' . $orderId
        ];
        $select = $connection->select()
            ->from($this->getMainTable(), TransactionInterface::ID)
            ->join(
                ['entity' => $this->getTable('aw_raf_transaction_entity')],
                implode(' AND ', $joinCondition),
                []
            )
            ->where($connection->quoteInto('action = ?', Action::ADVOCATE_EARNED_FOR_FRIEND_ORDER))
            ->where($connection->quoteInto('status = ?', Status::PENDING));
        return (int)$connection->fetchOne($select);
    }
}
