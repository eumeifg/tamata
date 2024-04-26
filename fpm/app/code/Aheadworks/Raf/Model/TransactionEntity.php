<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model;

use Aheadworks\Raf\Api\Data\TransactionEntityInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Transaction
 * @package Aheadworks\Raf\Model
 */
class TransactionEntity extends AbstractExtensibleObject implements TransactionEntityInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return  $this->_get(self::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return  $this->_get(self::ENTITY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityType($entityType)
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityLabel()
    {
        return $this->_get(self::ENTITY_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityLabel($entityLabel)
    {
        return $this->setData(self::ENTITY_LABEL, $entityLabel);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Aheadworks\Raf\Api\Data\TransactionEntityExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
