<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface TransactionEntityInterface
 * @api
 */
interface TransactionEntityInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const TRANSACTION_ID = 'transaction_id';
    const ENTITY_TYPE = 'entity_type';
    const ENTITY_ID = 'entity_id';
    const ENTITY_LABEL = 'entity_label';
    /**#@-*/

    /**
     * Get transaction id
     *
     * @return int
     */
    public function getTransactionId();

    /**
     * Set transaction id
     *
     * @param  int $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId);

    /**
     * Get entity type
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Set entity type
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType);

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity id
     *
     * @param  int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get entity label
     *
     * @return string|null
     */
    public function getEntityLabel();

    /**
     * Set entity label
     *
     * @param string $entityLabel
     * @return $this
     */
    public function setEntityLabel($entityLabel);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Raf\Api\Data\TransactionEntityExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Raf\Api\Data\TransactionEntityExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\raf\Api\Data\TransactionEntityExtensionInterface $extensionAttributes
    );
}
