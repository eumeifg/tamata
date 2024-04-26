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
namespace Magedelight\Catalog\Model\Indexer\Product\Vendor;

use Magento\Catalog\Model\Category;

/**
 * Abstract action reindex class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractAction
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Resource instance
     *
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magedelight\Catalog\Model\ResourceModel\Indexer\VendorProductFactory
     */
    protected $_indexerFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Stock Indexer models per product type
     * Sorted by priority
     *
     * @var array
     */
    protected $_indexers = [];

    /**
     * Flag that defines if need to use "_idx" index table suffix instead of "_tmp"
     *
     * @var bool
     */
    protected $_isNeedUseIdxTable = false;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magedelight\Catalog\Model\ResourceModel\Indexer\VendorProductFactory $indexerFactory
     * @param \Magento\Framework\Indexer\CacheContext $cacheContext
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magedelight\Catalog\Model\ResourceModel\Indexer\VendorProductFactory $indexerFactory,
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_resource = $resource;
        $this->_indexerFactory = $indexerFactory;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     *
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Retrieve connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resource->getConnection();
        }
        return $this->_connection;
    }

    /**
     * Retrieve vendor product Indexer Models per Product Type
     *
     * @return \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\StockInterface[]
     */
    protected function _getTypeIndexers()
    {
        if (empty($this->_indexers)) {
            $indexerClassName = '';
            $indexer = $this->_indexerFactory->create($indexerClassName);
            $this->_indexers = $indexer;
        }
        return $this->_indexers;
    }

    /**
     * Returns table name for given entity
     *
     * @param string $entityName
     * @return string
     */
    protected function _getTable($entityName)
    {
        return $this->_resource->getTableName($entityName);
    }

    /**
     * Retrieve product relations by children
     *
     * @param int|array $childIds
     * @return array
     */
    public function getRelationsByChild($childIds)
    {
        $connection = $this->_getConnection();
        $select = $connection->select()
            ->from($this->_getTable('catalog_product_relation'), 'parent_id')
            ->where('child_id IN(?)', $childIds);

        return $connection->fetchCol($select);
    }

    /**
     * Reindex all
     *
     * @return void
     */
    public function reindexAll()
    {
        //$this->logger->addDebug('reindexAll-AbsAction');
        $this->useIdxTable(true);
        $this->clearTemporaryIndexTable();
        //$this->logger->addDebug('_getTypeIndexers-_reindexAll');
        $this->_getTypeIndexers()->reindexAll();
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @return $this
     */
    protected function _syncData()
    {
        $idxTableName = $this->_getIdxTable();
        $tableName = $this->_getTable('cataloginventory_stock_status');

        $this->_deleteOldRelations($tableName);

        $columns = array_keys($this->_connection->describeTable($idxTableName));
        $select = $this->_connection->select()->from($idxTableName, $columns);
        $query = $select->insertFromSelect($tableName, $columns);
        $this->_connection->query($query);
        return $this;
    }

    /**
     * Delete old relations
     *
     * @param string $tableName
     *
     * @return void
     */
    protected function _deleteOldRelations($tableName)
    {
        $select = $this->_connection->select()
            ->from(['s' => $tableName])
            ->joinLeft(
                ['w' => $this->_getTable('catalog_product_website')],
                's.product_id = w.product_id AND s.website_id = w.website_id',
                []
            )
            ->where('w.product_id IS NULL');

        $sql = $select->deleteFromSelect('s');
        $this->_connection->query($sql);
    }

    /**
     * Refresh entities index
     *
     * @param array $marketplaceProductIds
     * @return array Affected ids
     */
    protected function _reindexRows($marketplaceProductIds = [])
    {
        $connection = $this->_getConnection();

        if (!is_array($marketplaceProductIds)) {
            $marketplaceProductIds = [$marketplaceProductIds];
        }

        $processIds = $marketplaceProductIds;

        $pairs = $processIds;

        $indexer = $this->_getTypeIndexers();
        //$this->logger->addDebug('_getTypeIndexers-_reindexRows');
        $indexer->reindexEntity($pairs);

        /* this part is keep pending. we need to clear cache for individual product */

        $select = $connection->select()
            ->distinct(true)
            ->from($this->_getTable('catalog_category_product'), ['category_id'])
            ->where('product_id IN(?)', $processIds);
        //$this->logger->debug('cacheQuery-'.$select);
        $affectedCategories = $connection->fetchCol($select);
        $this->cacheContext->registerEntities(Category::CACHE_TAG, $affectedCategories);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $processIds);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

        return $this;
    }

    /**
     * Set or get what either "_idx" or "_tmp" suffixed temporary index table need to use
     *
     * @param bool|null $value
     * @return bool
     */
    public function useIdxTable($value = null)
    {
        if ($value !== null) {
            $this->_isNeedUseIdxTable = (bool)$value;
        }
        return $this->_isNeedUseIdxTable;
    }

    /**
     * Retrieve temporary index table name
     *
     * @return string
     */
    protected function _getIdxTable()
    {
        if ($this->useIdxTable()) {
            return $this->_getTable('md_vendor_product_listing_idx');
        }
        return $this->_getTable('md_vendor_product_listing_idx');
    }

    /**
     * Clean up temporary index table
     *
     * @return void
     */
    public function clearTemporaryIndexTable()
    {
        $this->_getConnection()->delete($this->_getIdxTable());
        //$this->logger->addDebug($this->_getIdxTable());
    }
}
