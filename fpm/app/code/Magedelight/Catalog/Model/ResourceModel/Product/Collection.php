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
namespace Magedelight\Catalog\Model\ResourceModel\Product;

use Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria;
use Magedelight\Catalog\Model\Product;
use Magedelight\Sales\Model\Order as VendorOrder;
use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'vendor_product_id';
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vendor_prod_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'vendor_prod_collection';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magedelight\Catalog\Model\Config\Source\DefaultVendor\Criteria
     */
    protected $criteriaConfig;

    /**
     * Max prise (statistics data)
     *
     * @var float
     */
    protected $maxPrice;

    /**
     * Min prise (statistics data)
     *
     * @var float
     */
    protected $minPrice;
    /**
     * Special Min price (statistics data)
     *
     * @var float
     */
    protected $minSpecialPrice;

    protected $ratingAvg;

    protected $totalSelling;
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Criteria $criteriaConfig
     * @param TimezoneInterface $timezone
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Criteria $criteriaConfig,
        TimezoneInterface $timezone,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->criteriaConfig = $criteriaConfig;
        $this->timezone = $timezone;
    }

    public function _construct()
    {
        $this->_init(
            \Magedelight\Catalog\Model\Product::class,
            \Magedelight\Catalog\Model\ResourceModel\Product::class
        );
    }

    /**
     * Get products max price
     *
     * @return float
     */
    public function getMaxPrice($marketplaceProductId = null)
    {
        if ($this->maxPrice === null) {
            $this->_prepareStatisticsData($marketplaceProductId);
        }

        return $this->maxPrice;
    }

    /**
     * Get products min price
     *
     * @return float
     */
    public function getMinPrice($marketplaceProductId = null)
    {
        if ($this->minPrice === null) {
            $this->_prepareStatisticsData($marketplaceProductId);
        }
        return $this->minPrice;
    }

    /**
     * Get products min price
     *
     * @return float
     */
    public function getMinSpecialPrice($marketplaceProductId = null, $vendorId = null)
    {
        if ($this->minSpecialPrice === null) {
            $this->_prepareStatisticsData($marketplaceProductId, $vendorId, true);
        }
        return $this->minSpecialPrice;
    }

    /**
     * Prepare statistics data
     *
     * @return $this
     */
    protected function _prepareStatisticsData($marketplaceProductId = null, $vendorId = null, $isSpecialPrice = false)
    {
        $currentWebsiteId = ($this->storeManager->getStore()->getWebsiteId()) ?: 0;
        /**
         * Changes RH : Speed Test
         */
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $mdProductFactory = $objectManager->create(\Magedelight\Catalog\Model\Product::class);
        // $mdProduct = $mdProductFactory->getCollection();
        // if ($marketplaceProductId) {
        //     $mdProduct->addFieldToFilter('marketplace_product_id', ['eq' => $marketplaceProductId]);
        // }
        // if ($vendorId) {
        //     $mdProduct->addFieldToFilter('vendor_id', ['eq' => $vendorId]);
        // }
        // if ($isSpecialPrice) {
        //     $now = date('Y-m-d H:i:s');
        //     $mdProduct->addFieldToFilter('special_from_date', ['gteq' => strtotime($now)])->addFieldToFilter('special_to_date', ['lteq' => strtotime($now)]);

        // }
        // $mdProduct->getSelect()->columns(['qty_sum' => new \Zend_Db_Expr('SUM(qty)')])
        //     ->columns(['min_price' => new \Zend_Db_Expr('MIN(rbvpw.price)')])
        //     ->columns(['max_price' => new \Zend_Db_Expr('MAX(rbvpw.price)')])
        //     ->columns(['min_special_price' => new \Zend_Db_Expr('MIN(rbvpw.special_price)')])
        //     ->group('marketplace_product_id');
        // $row = [];
        // foreach ($mdProduct as $key => $value) {
        //     $row[] = $value['vendor_product_id'];
        //     $row[] = $value['marketplace_product_id'];
        //     $row[] = $value['qty_sum'];
        //     $row[] = $value['min_price'];
        //     $row[] = $value['max_price'];
        //     $row[] = $value['min_special_price'];
        //     $row[] = $value['status'];

        // }
        /**
         * Changes RH : Speed Test
         */

        $connection = $this->_resource->getConnection();
        $mainTableName = $this->_resource->getTable('md_vendor_product');
        $websiteTableName = $this->_resource->getTable('md_vendor_product_website');
        $select = $connection->select()
            ->from(
                ['rvp' => $mainTableName],
                [
                    'vendor_product_id',
                    'marketplace_product_id',
                    'SUM(qty)',
                    'MIN(rbvpw.price)',
                    'MAX(rbvpw.price)',
                    'MIN(rbvpw.special_price)',
                ]
            )->joinLeft(
            ['rbvpw' => $websiteTableName],
            '(rvp.vendor_product_id = rbvpw.vendor_product_id and rbvpw.status = "' . Product::STATUS_LISTED . '")',
            ['status']
        )->where(
            'rbvpw.website_id = (SELECT website_id from '
            . $websiteTableName . ' where marketplace_product_id = '
            . $marketplaceProductId . ' group by marketplace_product_id)'
        );
        if ($marketplaceProductId) {
            $select->where('rvp.marketplace_product_id = ?', $marketplaceProductId);
        }

        if ($vendorId) {
            $select->where('rvp.vendor_id = ?', $vendorId);
        }

        if ($isSpecialPrice) {
            $storeDate = $this->getCurrentWebsiteTime();
            $select->where('('.$storeDate.' between rbvpw.special_from_date AND rbvpw.special_to_date)');
        }

        $select->where('rbvpw.website_id = ?', $currentWebsiteId);

        $row = $this->getConnection()->fetchRow($select, $this->_bindParams, \Zend_Db::FETCH_NUM);

        $this->maxPrice = (double) $row[4]; /*set product maximum price from all offers*/
        $this->minPrice = (double) $row[3]; /*set product minimum price from all offers*/

        if ($row[5] == '' || $row[5] == null) {
            $this->minSpecialPrice = (double) $row[4]; /*set product special price */
        } else {
            $this->minSpecialPrice = (double) $row[5]; /*set product special price */
        }

        return $this;
    }

    /**
     *
     * @param type $marketplaceProductId
     * @return type
     */
    public function getTotalProductCount($marketplaceProductId = null)
    {
        $quantity = $this->_prepareStockData($marketplaceProductId);
        return $quantity;
    }

    /**
     *
     * @param $condition
     * @param $columnData
     * @return int
     */
    public function setTableRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('md_vendor_product'),
            $columnData,
            $where = $condition
        );
    }

    /**
     *
     * @param type $marketplaceProductId
     * @return
     */
    protected function _prepareStockData($marketplaceProductId = null)
    {
        $connection = $this->_resource->getConnection();
        $mainTableName = $this->_resource->getTable('md_vendor_product');
        $websiteTableName = $this->_resource->getTable('md_vendor_product_website');
        $select = $connection->select()
            ->from(
                ['rvp' => $mainTableName],
                ['vendor_product_id', 'marketplace_product_id', 'SUM(qty)']
            )->joinLeft(
            ['rbvpw' => $websiteTableName],
            'rvp.vendor_product_id = rbvpw.vendor_product_id AND rbvpw.status = "' . Product::STATUS_LISTED . '"',
            ['status']
        )->where(
            'rbvpw.website_id IN (SELECT website_id from '
            . $websiteTableName . ' where vendor_product_id IN (Select vendor_product_id from '
            . $mainTableName . ' where marketplace_product_id = ' . $marketplaceProductId . '))'
        );
        if ($marketplaceProductId) {
            $select->where('rvp.marketplace_product_id = ?', $marketplaceProductId);
        }
        $row = $this->getConnection()->fetchRow($select, $this->_bindParams, \Zend_Db::FETCH_NUM);
        $qty = $row[2];
        return $qty;
    }

    /**
     * @param $collection
     * @param bool $addQtyFilter
     * @param array $vendorStatusFilter
     * @param bool $addRatingData
     * @param bool $addSalesData
     * @param bool $addStatusFilter
     * @param bool $addCatalogRulePriceData
     */
    public function processCollectionForFrontend(
        $collection,
        $addQtyFilter = true,
        $vendorStatusFilter = [VendorStatus::VENDOR_STATUS_ACTIVE],
        $addRatingData = true,
        $addSalesData = true,
        $addStatusFilter = true,
        $addCatalogRulePriceData = true
    ) {
        if ($addCatalogRulePriceData) {
            $this->addCatalogRulePriceData($collection);
        }
        $expression = $this->getSpecialPriceExpression();

        $collection->getSelect()->columns(
            [
                'special_price' => $expression,
            ]
        );
        if ($addRatingData) {
            $this->_addRatingData($collection);
        }
        if ($addSalesData) {
            $this->_addSalesData($collection);
        }

        $this->addWebsiteFilter($collection);
        $this->addFiltersOnCollection($collection, $addQtyFilter, $vendorStatusFilter);
        $this->processSorting($collection, $addRatingData);
        if ($addStatusFilter) {
            $this->addStatusFilter($collection, \Magedelight\Catalog\Model\Product::STATUS_LISTED);
        }
    }

    /**
     * @param $collection
     * @param bool $addQtyFilter
     * @param array $vendorStatusFilter
     * @param bool $addRatingData
     * @param bool $addSalesData
     * @param bool $addStatusFilter
     * @param bool $addCatalogRulePriceData
     */
    public function processCollectionForFrontendForGraphQlPrice(
        $collection,
        $addQtyFilter = true,
        $vendorStatusFilter = [VendorStatus::VENDOR_STATUS_ACTIVE],
        $addRatingData = true,
        $addSalesData = true,
        $addStatusFilter = true,
        $addCatalogRulePriceData = true
    ) {
        if ($addCatalogRulePriceData) {
            $this->addCatalogRulePriceData($collection);
        }
        $expression = $this->getSpecialPriceExpression();

        $collection->getSelect()->columns(
            [
                'special_price' => $expression,
            ]
        );
//        if ($addRatingData) {
//            $this->_addRatingData($collection);
//        }
//        if ($addSalesData) {
//            $this->_addSalesData($collection);
//        }
//
//        $this->addWebsiteFilter($collection);
        $this->addFiltersOnCollection($collection, $addQtyFilter, $vendorStatusFilter);
        $this->processSorting($collection, $addRatingData);
        if ($addStatusFilter) {
            $this->addStatusFilter($collection, \Magedelight\Catalog\Model\Product::STATUS_LISTED);
        }
    }

    /**
     *
     * @param type $collection
     */
    public function addCatalogRulePriceData($collection)
    {
        return $collection;
    }

    /**
     *
     * @return string
     */
    public function getSpecialFromDateExpression()
    {
        $storeDate = $this->getCurrentWebsiteTime();
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  rbvpw.special_from_date is null
              AND rbvpw.special_to_date is not null
            THEN '".$storeDate."'
            ELSE rbvpw.special_from_date
          END)");
    }

    /**
     *
     * @return string
     */
    public function getSpecialToDateExpression()
    {
        $storeDate = $this->getCurrentWebsiteTime();
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  rbvpw.special_to_date is null
              AND rbvpw.special_from_date is not null
            THEN '".$storeDate."'
            ELSE rbvpw.special_to_date
          END)");
    }

    /**
     *
     * @return string
     */
    public function getSpecialPriceExpression()
    {
        $storeDate = $this->getCurrentWebsiteTime();
        return $expression = new \Zend_Db_Expr("(CASE
            WHEN  rbvpw.special_price is not null
              AND '".$storeDate."' >= IFNULL(" . $this->getSpecialFromDateExpression() . ", '".$storeDate."')
              AND '".$storeDate."' <= IFNULL(" . $this->getSpecialToDateExpression() . ", '".$storeDate."')
            THEN rbvpw.special_price
            ELSE rbvpw.price
          END)");
    }

    /**
     *
     * @param type $collection
     */
    protected function _addRatingData($collection)
    {
        $collection->getSelect()->columns(['rating_avg' => $this->getRatingAvg()]);
    }

    /**
     *
     *
     * @param type $collection
     */
    protected function _addSalesData($collection)
    {
        $collection->getSelect()->columns(['total_selling' => $this->getSalesData()]);
    }

    /**
     *
     * @param type $collection
     */
    protected function processSorting($collection, $addRatingData)
    {
        foreach ($this->criteriaConfig->getActiveCriteriasForDefaultVendor() as $criteria) {
            if ($criteria == Criteria::HIGHEST_RATING_CRITERIA && $addRatingData) {
                $this->sortByRatingAverage($collection);
            }
            if ($criteria == Criteria::LEAST_PRICE_CRITERIA) {
                $this->sortByLeastPrice($collection);
            }
            if ($criteria == Criteria::HIGHEST_SELLING_CRITERIA) {
                $this->sortByHighestSelling($collection);
            }
        }
    }

    /**
     *
     * @param type $collection
     */
    protected function sortByRatingAverage($collection)
    {
        $collection->getSelect()->order('rating_avg DESC');
    }

    /**
     *
     * @param type $collection
     */
    public function addWebsiteFilter($collection)
    {
        $websiteId = ($this->storeManager->getStore()->getWebsiteId()) ?
        $this->storeManager->getStore()->getWebsiteId() : 0;
        $collection->addFieldToFilter('rbvpw.website_id', ['eq' => $websiteId]);
    }

    /**
     *
     * @param type $collection
     * @param type $addQtyFilter
     */
    public function addFiltersOnCollection($collection, $addQtyFilter = true, $vendorStatusFilter = [1])
    {
        /*
         * If product type is configure than no need to filter it by qty
         */
        if ($addQtyFilter) {
            $collection->addFieldToFilter('main_table.qty', ['gt' => 0]);
        }
        $collection->addFieldToFilter('rvwd.status', ['in' => $vendorStatusFilter]);
    }

    /**
     *
     * @param type $collection
     */
    public function sortByLeastPrice($collection)
    {
        $collection->getSelect()->order('special_price ASC');
        $collection->getSelect()->order('rbvpw.price ASC');
    }

    /**
     *
     * @param type $collection
     */
    public function sortByHighestSelling($collection)
    {
        $collection->getSelect()->order('total_selling DESC');
    }

    /**
     *
     * @param type $collection
     * @param type $status
     */
    public function addStatusFilter($collection, $status)
    {
        $collection->addFieldToFilter('rbvpw.status', ['eq' => $status]);
    }

    /**
     *
     * @return type
     */
    protected function getRatingAvg()
    {
        if (!$this->ratingAvg) {
            $connection = $this->_resource->getConnection();
            $select = $connection->select()->from(
                ['rvrt' => $this->_resource->getTable('md_vendor_rating_rating_type')],
                [
                    '(SUM(`rvrt`.`rating_avg`)/(SELECT count(*) FROM '
                    . $this->_resource->getTable('md_vendor_rating') . ' where main_table.vendor_id = '
                    . $this->_resource->getTable('md_vendor_rating') . '.vendor_id AND '
                    . $this->_resource->getTable('md_vendor_rating') . '.is_shared = 1)  / 20)',
                ]
            );
            $select->joinInner(
                ['rvr' => $this->_resource->getTable('md_vendor_rating')],
                '`rvr`.`vendor_rating_id` = `rvrt`.`vendor_rating_id`',
                []
            );

            $select->where('`rvr`.`vendor_id` = `main_table`.`vendor_id` AND `rvr`.`is_shared` = 1');
            $this->ratingAvg = $select;
        }
        return $this->ratingAvg;
    }

    /**
     *
     * @return type
     */
    protected function getSalesData()
    {
        if (!$this->totalSelling) {
            $connection = $this->_resource->getConnection();
            $select = $connection->select()->from(
                ['order_item' => $this->_resource->getTable('sales_order_item')],
                [
                    'SUM(`order_item`.`qty_ordered`)',
                ]
            );

            $select->joinInner(
                ['vendor_order' => $this->_resource->getTable('md_vendor_order')],
                '`order_item`.`order_id` = `vendor_order`.`order_id` AND '
                . 'vendor_order.status = \'' . VendorOrder::STATUS_COMPLETE . '\'',
                []
            );

            $select->where('`order_item`.`vendor_id` = `main_table`.`vendor_id` '
                . 'AND `order_item`.`product_id` = `main_table`.`marketplace_product_id`');
            $select->group('order_item.vendor_id');
            $this->totalSelling = $select;
        }
        return $this->totalSelling;
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
     * @return string
     */
    public function getCurrentWebsiteTime(): string
    {
        return $this->timezone->date()->format('Y-m-d');
    }
}
