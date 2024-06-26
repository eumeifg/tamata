<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructureFactory;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

/**
 * Calculate minimal and maximal prices for Grouped products
 * Use calculated price for relation products
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped implements DimensionalIndexerInterface
{
    /**
     * @var IndexTableStructureFactory
     */
    private $indexTableStructureFactory;

    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var bool
     */
    private $fullReindexAction;

    /**
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param string $connectionName
     * @param bool $fullReindexAction
     */
    public function __construct(
        IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        $connectionName = 'indexer',
        $fullReindexAction = false
    ) {
        $this->indexTableStructureFactory = $indexTableStructureFactory;
        $this->tableMaintainer = $tableMaintainer;
        $this->connectionName = $connectionName;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->fullReindexAction = $fullReindexAction;
    }

    /**
     * {@inheritdoc}
     * @param array $dimensions
     * @param \Traversable $entityIds
     * @throws \Exception
     */
    public function executeByDimensions(array $dimensions, \Traversable $entityIds)
    {
        /** @var IndexTableStructure $temporaryPriceTable */
        $temporaryPriceTable = $this->indexTableStructureFactory->create([
            'tableName' => $this->tableMaintainer->getMainTmpTable($dimensions),
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'vendorField' => 'vendor_id',
            'originalPriceField' => 'price',
            'finalPriceField' => 'final_price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
//            'specialPriceField' => 'special_price',
//            'specialFromDateField' => 'special_from_date',
//            'specialToDateField' => 'special_to_date',
        ]);
        $query = $this->prepareGroupedProductPriceDataSelect($dimensions, iterator_to_array($entityIds))
            ->insertFromSelect($temporaryPriceTable->getTableName());
        $this->getConnection()->query($query);
    }

    /**
     * Prepare data index select for Grouped products prices
     *
     * @param array $dimensions
     * @param array $entityIds the parent entity ids limitation
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     */
    private function prepareGroupedProductPriceDataSelect(array $dimensions, array $entityIds)
    {
        $select = $this->getConnection()->select();

        $select->from(
            ['e' => $this->getTable('catalog_product_entity')],
            'entity_id'
        );

        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select->joinLeft(
            ['l' => $this->getTable('catalog_product_link')],
            'e.' . $linkField . ' = l.product_id AND l.link_type_id=' . Link::LINK_TYPE_GROUPED,
            []
        );
        //additional information about inner products
        $select->joinLeft(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.linked_product_id',
            []
        );
        $select->columns(
            [
                'i.customer_group_id',
                'i.website_id',
            ]
        );
        $taxClassId = $this->getConnection()->getCheckSql('MIN(i.tax_class_id) IS NULL', '0', 'MIN(i.tax_class_id)');
        $minCheckSql = $this->getConnection()->getCheckSql('le.required_options = 0', 'i.min_price', 0);
        $maxCheckSql = $this->getConnection()->getCheckSql('le.required_options = 0', 'i.max_price', 0);
        $select->join(
            ['i' => $this->getMainTable($dimensions)],
            'i.entity_id = l.linked_product_id',
            [
                'tax_class_id' => $taxClassId,
                'price' => new \Zend_Db_Expr('NULL'),
                'final_price' => new \Zend_Db_Expr('NULL'),
                'min_price' => new \Zend_Db_Expr('MIN(' . $minCheckSql . ')'),
                'max_price' => new \Zend_Db_Expr('MAX(' . $maxCheckSql . ')'),
                'tier_price' => new \Zend_Db_Expr('NULL'),
            ]
        );
        /* ------------------RB Customization ---------------*/
        $select ->joinLeft(
            ['rbvp' => $this->getTable('md_vendor_product_listing_idx')],
            'rbvp.marketplace_product_id = e.entity_id',
            ['vendor_id','special_price','special_from_date','special_to_date']
        );
        /* ------------------RB Customization ---------------*/
        $select->group(
            ['e.entity_id', 'i.customer_group_id', 'i.website_id']
        );
        $select->where(
            'e.type_id=?',
            GroupedType::TYPE_CODE
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Get main table
     *
     * @param array $dimensions
     * @return string
     */
    private function getMainTable($dimensions)
    {
        if ($this->fullReindexAction) {
            return $this->tableMaintainer->getMainReplicaTable($dimensions);
        }
        return $this->tableMaintainer->getMainTable($dimensions);
    }

    /**
     * Get connection
     *
     * return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \DomainException
     */
    private function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resource->getConnection($this->connectionName);
        }

        return $this->connection;
    }

    /**
     * Get table
     *
     * @param string $tableName
     * @return string
     */
    private function getTable($tableName)
    {
        return $this->resource->getTableName($tableName, $this->connectionName);
    }
}
