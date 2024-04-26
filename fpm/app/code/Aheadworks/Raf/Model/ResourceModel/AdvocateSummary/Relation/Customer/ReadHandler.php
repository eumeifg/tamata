<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\ResourceModel\AdvocateSummary\Relation\Customer;

use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Raf\Model\ResourceModel\AdvocateSummary\Relation\Customer
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var string
     */
    private $customerTableName;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(MetadataPool $metadataPool, ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->customerTableName = $this->resourceConnection->getTableName('customer_entity');
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFoRaflParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var AdvocateSummaryInterface $entity */
        $entityId = (int)$entity->getId();
        if (!$entityId) {
            return $entity;
        }

        $customer = $this->getCustomerData($entity->getCustomerId());
        $entity->setCustomerName(
            $customer[CustomerInterface::FIRSTNAME] .
            ' ' .
            $customer[CustomerInterface::LASTNAME]
        );
        $entity->setCustomerEmail($customer[CustomerInterface::EMAIL]);

        return $entity;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \Exception
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnectionByName(
            $this->metadataPool->getMetadata(AdvocateSummaryInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Retrieve customer data
     *
     * @param int $entityId
     * @return array
     * @throws \Exception
     */
    public function getCustomerData($entityId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->customerTableName)
            ->where('entity_id = :id');
        return $connection->fetchRow($select, ['id' => $entityId]);
    }
}
