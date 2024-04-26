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
namespace Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DB\Select;

class DefaultVendors extends AbstractIndexer implements VendorProductInterface
{

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Criteria
     */
    protected $criteriaConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $_defaultVendorAttributeId = null;

    protected $ratingAvg = null;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Criteria $criteriaConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        Criteria $criteriaConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        $connectionName = null
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->eavAttribute = $eavAttribute;
        $this->criteriaConfig = $criteriaConfig;
        parent::__construct($context, $tableStrategy, $eavConfig, $connectionName);
    }

    /**
     * Initialize connection and define main table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('md_vendor_product', 'vendor_product_id');
    }

    /**
     * Reindex all stock status data for default logic product type
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        $this->beginTransaction();
        try {
            $this->_prepareIndexTable();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Reindex vendor product collection for defined product ids
     *
     * @param int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds)
    {
        $this->_updateIndex($entityIds);
        return $this;
    }

    /**
     * Prepare select query object to get highest rating & lowest price (default vendor).
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getProductVendorSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $select = $this->_getSimpleProductSelect($entityIds);

        $selectMain = $connection->select()->from(
            ['rvp' => $select],
            [
                    'marketplace_product_id',
                    'parent_id',
                    'vendor_id',
                    'website_id',
                    'price',
                    'special_price',
                    'special_from_date',
                    'special_to_date',
                    'qty'
                ]
        );
        $selectMain->group(['rvp.marketplace_product_id', 'rvp.website_id']);

        return $selectMain;
    }

    /**
     *
     * @param string $entityIds
     * @return type
     */
    protected function _getSimpleProductSelect($entityIds = null)
    {
        $connection = $this->getConnection();
        $expression = $this->getSpecialPriceExpression();

        $select = $connection->select()->from(
            ['main_table' => $this->getTable('md_vendor_product')],
            [
                  'marketplace_product_id',
                  'parent_id',
                  'vendor_id',
                  'qty',
                  'rating_avg' => $this->getRatingSelect(),
                  'total_selling' => $this->getSalesSelect(),
                ]
        )->joinLeft(
            ['cpe' => 'catalog_product_entity'],
            'cpe.entity_id = main_table.marketplace_product_id',
            ['cpe.row_id']
        )->joinLeft(
            ['rbvpw' => $this->getTable('md_vendor_product_website')],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            [
                'price',
                'special_price'  => $expression,
                'special_from_date',
                'special_to_date',
                'website_id' => 'website_id'
            ]
        );

        $select->joinInner(['rbv' => $this->getTable('md_vendor')], 'main_table.vendor_id = rbv.vendor_id', []);
        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = rbv.vendor_id and rvwd.status = 1',
            ['vendor_website'=>'rvwd.website_id']
        );
        $select = $this->joinExtraTables($select);

        $select->where('rbvpw.status = 1')
                ->where('main_table.is_deleted = 0')
                // ->where('main_table.qty > 0')
                ->where('main_table.type_id = \'' . Type::TYPE_SIMPLE . '\'');

        if ($entityIds !== null) {
            $select->where('main_table.marketplace_product_id IN(?)', $entityIds);
        }

        $select->group(['main_table.marketplace_product_id', 'main_table.vendor_id','rbvpw.website_id']);

        $this->processSorting($select);

        return $select;
    }

    /**
     * Get average rating select.
     * @return type
     */
    protected function getRatingSelect()
    {
        $select = $this->getConnection()->select()->from(
            ['rvrt' => $this->getTable('md_vendor_rating_rating_type')],
            [
                'avg_rating' => 'COALESCE(AVG(rvrt.rating_value), 0)'
            ]
        );
        $select->joinInner(
            ['rvr' => $this->getTable('md_vendor_rating')],
            '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
            []
        );
        $select->where('`rvr`.`vendor_id` = `main_table`.`vendor_id`')->where('`rvr`.`is_shared` =1');
        return $select;
    }

    /**
     * Get average rating select.
     * @return type
     */
    protected function getSalesSelect()
    {
        $select = $this->getConnection()->select()->from(
            ['order_item' => $this->getTable('sales_order_item')],
            [
              'SUM(`order_item`.`qty_ordered`)'
            ]
        );

        $select->joinInner(
            ['vendor_order' => $this->getTable('md_vendor_order')],
            '`order_item`.`order_id` = `vendor_order`.`order_id` AND '
                . 'vendor_order.status = \'' . VendorOrder::STATUS_COMPLETE . '\'',
            []
        );

        $select->where('`order_item`.`vendor_id` = `main_table`.`vendor_id` '
                . 'AND `order_item`.`product_id` = `main_table`.`marketplace_product_id`');
        $select->group('order_item.vendor_id');
        return $select;
    }

    /**
     * @param type $select
     * @param string $type
     */
    protected function processSorting($select, $type = Type::TYPE_SIMPLE)
    {
        foreach ($this->criteriaConfig->getActiveCriteriasForDefaultVendor() as $criteria) {
            if ($criteria == Criteria::HIGHEST_RATING_CRITERIA) {
                $this->sortByRatingAverage($select, $type);
            }

            if ($criteria == Criteria::LEAST_PRICE_CRITERIA) {
                $this->sortByLeastPrice($select, $type);
            }

            if ($criteria == Criteria::HIGHEST_SELLING_CRITERIA) {
                $this->sortByHighestSelling($select);
            }
        }
    }

    /**
     *
     * @param type $select
     */
    protected function sortByHighestSelling($select)
    {
        $select->order('total_selling ' . Select::SQL_DESC);
    }

    /**
     *
     * @param type $select
     * @param string $type
     */
    protected function sortByRatingAverage($select, $type)
    {
        if ($type == Type::TYPE_SIMPLE) {
            $select->order('rating_avg ' . Select::SQL_DESC);
        } elseif ($type == Configurable::TYPE_CODE) {
            $select->order('average_rating ' . Select::SQL_DESC);
        }
    }

    /**
     *
     * @param type $select
     * @param string $type
     */
    protected function sortByLeastPrice($select, $type)
    {
        if ($type == Type::TYPE_SIMPLE) {
            $select->order('special_price ' . Select::SQL_ASC)
            ->order('rbvpw.price ' . Select::SQL_ASC)
            ->order('main_table.qty ' . Select::SQL_DESC);
        } elseif ($type == Configurable::TYPE_CODE) {
            $select->order('rbvpw.special_price ' . Select::SQL_ASC)
            ->order('rbvpw.price ' . Select::SQL_ASC)
            ->order('main_table.qty ' . Select::SQL_DESC);
        }
    }

    /**
     * @param type $entityIds
     * @param type $usePrimaryTable
     * @return type
     */
    protected function _getAttributeSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $select = $this->_getSimpleProductSelect($entityIds);
        $selectMain = $connection->select()->from(
            ['rvp' => $select],
            [
                    'row_id',
                    'marketplace_product_id',
                    'vendor_id'
                ]
        );
        $selectMain->group('rvp.marketplace_product_id');

        return $selectMain;
    }

    /**
     * Get the select object for get vendor products for configurable type
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getProductVendorSelectConfigurable($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $expression = $this->getSpecialPriceExpression();

        $select = $connection->select()->from(
            ['main_table' => $this->getTable('md_vendor_product')],
            [
                    'parent_id as marketplace_product_id',
                    'marketplace_product_id as parent_id',
                    'vendor_id',
                    'qty',
                    'total_selling' => $this->getSalesSelect(),
                ]
        )->joinLeft(
            ['rbvpw' => $this->getTable('md_vendor_product_website')],
            "main_table.vendor_product_id = rbvpw.vendor_product_id",
            [
                'price',
                'special_price'  => $expression,
                'special_from_date',
                'special_to_date',
                'website_id' => 'website_id'
            ]
        )->group(
            ['parent_id', 'vendor_id', 'marketplace_product_id']
        );

        $select->joinInner(['rbv' => $this->getTable('md_vendor')], 'main_table.vendor_id = rbv.vendor_id', []);
        $select->join(
            ['rvwd'=>'md_vendor_website_data'],
            'rvwd.vendor_id = rbv.vendor_id and rvwd.status = 1',
            ['vendor_website'=>'rvwd.website_id']
        );

        /* Rating select */
        $select->columns(['average_rating' => $this->getRatingAvg()]);
        /* Rating select */

        $select = $this->joinExtraTables($select);

        $select->where('main_table.is_deleted = 0')
                ->where('main_table.parent_id IS NOT NULL')
                // ->where('main_table.qty > 0')
                ->where('rbvpw.status = 1');

        if ($entityIds !== null) {
            $select->where('main_table.marketplace_product_id IN(?)', $entityIds);
            $this->processSorting($select, Configurable::TYPE_CODE);
            $select->limit(1);
        } else {
            $this->processSorting($select, Configurable::TYPE_CODE);
        }

        /* $this->logger->debug($select); */
        $selectMain = $connection->select()->from(
            ['rvp' => $select],
            [
                    'marketplace_product_id',
                    'parent_id',
                    'vendor_id',
                    'website_id',
                    'price',
                    'special_price',
                    'special_from_date',
                    'special_to_date',
                    'qty'
                ]
        );

        return $selectMain;
    }

    /**
     *
     * @return type
     */
    protected function getRatingAvg()
    {
        if (!$this->ratingAvg) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                ['rvrt' => $this->getTable('md_vendor_rating_rating_type')],
                [
                      '(SUM(`rvrt`.`rating_avg`)/(SELECT count(*) FROM '
                      . $this->getTable('md_vendor_rating') . ' where main_table.vendor_id = '
                      . $this->getTable('md_vendor_rating') . '.vendor_id AND '
                      . $this->getTable('md_vendor_rating') . '.is_shared = 1)  / 20)'
                ]
            );
            $select->joinInner(
                ['rvr' => $this->getTable('md_vendor_rating')],
                '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
                []
            );

            $select->where('`rvr`.`vendor_id` = `main_table`.`vendor_id` AND `rvr`.`is_shared` = 1');
            $this->ratingAvg = $select;
        }
        return $this->ratingAvg;
    }

    /**
     * Prepare vendor product collection data in index table
     *
     * @param int|array $entityIds the product limitation
     * @return $this
     */
    protected function _prepareIndexTable($entityIds = null)
    {
        $connection = $this->getConnection();
        /* select data for simple product */
        $select = $this->_getProductVendorSelect($entityIds);
        $query = $select->insertFromSelect($this->getIdxTable());
        $connection->query($query);

        /* select data for configurable product */
        $selectConfigurable = $this->_getProductVendorSelectConfigurable($entityIds);
        $queryConfigurable = $selectConfigurable->insertFromSelect($this->getIdxTable());
        $connection->query($queryConfigurable);

        $attributeSelect = $this->_getAttributeSelect($entityIds);
        $this->_indexAttributeTable($attributeSelect);

        return $this;
    }

    /**
     * Update vendor product index by vendor product ids
     *
     * @param array|int $entityIds
     * @return $this
     */
    protected function _updateIndex($entityIds)
    {
        $connection = $this->getConnection();
        $select = $this->_getProductVendorSelect($entityIds, true);
        $query = $connection->query($select);

        $i = 0;
        $data = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $i++;
            $data[] = [
                //'vendor_product_id' => (int) $row['vendor_product_id'],
                'marketplace_product_id' => (int) $row['marketplace_product_id'],
                'vendor_id'              => (int) $row['vendor_id'],
                'website_id'              => (int) $row['website_id'],
                'price'                  => $row['price'],
                'special_price'          => $row['special_price'],
                'qty'                    => (int) $row['qty'],
                'special_from_date' => $row['special_from_date'],
                'special_to_date' => $row['special_to_date'],
            ];

            if ($i % 1000 == 0) {
                $this->_updateIndexTable($data);
                $data = [];
            }
        }

        if (empty($data)) {
            $connection->delete(
                $this->getTable('md_vendor_product_listing_idx'),
                ['marketplace_product_id IN (?)' => $entityIds]
            );
            return $this;
        }
        $connection->delete(
            $this->getTable('md_vendor_product_listing_idx'),
            ['marketplace_product_id IN (?)' => $entityIds]
        );
        $this->_updateIndexTable($data);

        /* following creteria is only for configurable product*/

        $selectConfig = $this->_getProductVendorSelectConfigurable($entityIds, true);
        $queryConfig = $connection->query($selectConfig);

        $i = 0;
        $data = [];
        while ($row = $queryConfig->fetch(\PDO::FETCH_ASSOC)) {
            $i++;
            $data[] = [
                'marketplace_product_id' => (int) $row['marketplace_product_id'],
                'vendor_id' => (int) $row['vendor_id'],
                'website_id' => (int) $row['website_id'],
                'price' => (double) $row['price'],
                'special_price' => (double) $row['special_price'],
                'qty' => (int) $row['qty'],
                'special_from_date' => $row['special_from_date'],
                'special_to_date' => $row['special_to_date'],
            ];

            if ($i % 1000 == 0) {
                $this->_updateIndexTable($data);
                $data = [];
            }
        }

        //Removed simple product entityids from array.
        if (!empty($data)) {
            $ids = [];
            foreach ($data as $_data) {
                $ids[] = $_data['marketplace_product_id'];
            }
            $entityIds = array_intersect($entityIds, $ids);
        }
        if (empty($data)) {
            return $this;
        }

        $this->_updateIndexTable($data);

        $attributeSelect = $this->_getAttributeSelect($entityIds);
        $this->_indexAttributeTable($attributeSelect);

        return $this;
    }

    /**
     * Update stock status index table (INSERT ... ON DUPLICATE KEY UPDATE ...)
     *
     * @param array $data
     * @return $this
     */
    protected function _updateIndexTable($data)
    {
        if (empty($data)) {
            return $this;
        }

        $connection = $this->getConnection();
        $connection->insertOnDuplicate(
            $this->getTable('md_vendor_product_listing_idx'),
            $data,
            [
                'marketplace_product_id',
                'vendor_id',
                'website_id',
                'price',
                'special_price',
                'qty',
                'special_from_date',
                'special_to_date'
            ]
        );
        return $this;
    }

    /**
     * @param type $attributeSelect
     */
    protected function _indexAttributeTable($attributeSelect)
    {
        $this->_updateAttributeTable($attributeSelect);
    }

    /**
     *
     * @param type $attributeSelect
     * @return \Magedelight\Catalog\Model\ResourceModel\Indexer\Vendor\DefaultVendors
     */
    protected function _updateAttributeTable($attributeSelect)
    {
        $attributeSelect->columns(
            [
                'entity_id'      => 'marketplace_product_id',
                'store_id'       => 0,
                'attribute_id'   => $this->getDefaultVendorAttributeId(),
                'value'          => 'vendor_id'
            ]
        );

        $connection = $this->getConnection();
        $connection->insertFromSelect(
            $attributeSelect,
            $this->getTable('catalog_product_entity_int'),
            ['attribute_id','store_id','row_id', 'value'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        return $this;
    }

    /**
     * Retrieve temporary index table name
     *
     * @param string $table
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIdxTable($table = null)
    {
        return $this->tableStrategy->getTableName('md_vendor_product_listing');
    }
    /**
     * Get Product price from Index Table
     * @param type $productId
     * @return product price
     */
    public function getPriceByProductId($productId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getTable('md_vendor_product_listing_idx'), 'price')
            ->where('marketplace_product_id = :marketplace_product_id');

        $bind = [':marketplace_product_id' => (int)$productId];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * @param type $productId
     * @return type
     */
    public function getSpecialPriceByProductId($productId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getTable('md_vendor_product_listing_idx'), 'special_price')
            ->where('marketplace_product_id = :marketplace_product_id');

        $bind = [':marketplace_product_id' => (int)$productId];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Obtain all marketplace product id of selected vendor
     * @param int $vendorId
     * @return collection
     */
    public function getVendorProductsById($vendorId = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('md_vendor_product_listing_idx'), 'marketplace_product_id')
            ->where('vendor_id = :vendor_id');

        $bind = [':vendor_id' => (int)$vendorId];
        return $connection->fetchAll($select, $bind);
    }
    /**
     *
     * @param array $vendorIds
     * @return array
     */
    public function getVendorProductsByIds($vendorIds = [])
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['rbpidx' => $this->getTable('md_vendor_product_listing_idx')],
                ['marketplace_product_id']
            )
            ->where('rbpidx.vendor_id IN (?)', array_values($vendorIds));

        if (!($productIds = $connection->fetchCol($select))) {
            $productIds = [];
        }

        return $productIds;
    }

    /**
     * @param type $productId
     * @return type
     */
    public function getProductQty($productId = false)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getTable('md_vendor_product_listing_idx'), 'qty')
            ->where('marketplace_product_id = :marketplace_product_id');

        $bind = [':marketplace_product_id' => (int)$productId];

        $data = $connection->fetchRow($select, $bind);
        return $data['qty'];
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return type
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     *
     * @param type $productId
     * @return type
     */
    public function getConfigProductPriceByProductId($productId)
    {
        $connection = $this->getConnection();
        $websiteId = $this->getWebsiteId();
        $select = $connection->select()
            ->from($this->getTable('catalog_product_index_price'), 'price')
            ->where("entity_id = $productId AND website_id = $websiteId");
        return $connection->fetchOne($select);
    }

    /**
     * @param integer $productId
     * @return string
     */
    public function getConfigProductSpecialPriceByProductId($productId)
    {
        $connection = $this->getConnection();
        $websiteId = $this->getWebsiteId();
        $select = $connection->select()
            ->from($this->getTable('catalog_product_index_price'), 'special_price')
            ->where("entity_id = $productId AND website_id = $websiteId");
        return $connection->fetchOne($select);
    }

    /**
     *
     * @return type
     */
    public function getSpecialPriceExpression()
    {
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  rbvpw.special_price is not null
              AND ((curdate() >= rbvpw.special_from_date
              AND curdate() <= coalesce(rbvpw.special_to_date, curdate())) OR 
              (rbvpw.special_from_date is null AND rbvpw.special_to_date is null))
            THEN rbvpw.special_price
            ELSE rbvpw.price
          END)");
    }

    /**
     *
     * @param type $select
     * @return type
     */
    public function joinExtraTables($select)
    {
        return $select;
    }

    /**
     *
     * @return type
     */
    public function getDefaultVendorAttributeId()
    {
        if (!$this->_defaultVendorAttributeId) {
            $this->_defaultVendorAttributeId = $this->eavAttribute->getIdByCode(
                \Magento\Catalog\Model\Product::ENTITY,
                \Magedelight\Vendor\Model\Vendor::DEFAULT_VENDOR_ATTRIBUTE
            );
        }
        return $this->_defaultVendorAttributeId;
    }
}
