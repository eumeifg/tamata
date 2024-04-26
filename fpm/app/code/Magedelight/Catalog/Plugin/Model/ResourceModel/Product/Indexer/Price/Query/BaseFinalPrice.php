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
namespace Magedelight\Catalog\Plugin\Model\ResourceModel\Product\Indexer\Price\Query;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpression;
use Magento\Framework\Indexer\Dimension;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\JoinAttributeProcessor;

class BaseFinalPrice
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var JoinAttributeProcessor
     */
    private $joinAttributeProcessor;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Mapping between dimensions and field in database
     *
     * @var array
     */
    private $dimensionToFieldMapper = [
        WebsiteDimensionProvider::DIMENSION_NAME => 'pw.website_id',
        CustomerGroupDimensionProvider::DIMENSION_NAME => 'cg.customer_group_id',
    ];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * BaseFinalPrice constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param JoinAttributeProcessor $joinAttributeProcessor
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        JoinAttributeProcessor $joinAttributeProcessor,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = 'indexer'
    ) {
        $this->resource = $resource;
        $this->connectionName = $connectionName;
        $this->joinAttributeProcessor = $joinAttributeProcessor;
        $this->moduleManager = $moduleManager;
        $this->eventManager = $eventManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice $subject
     * @param \Closure $proceed
     * @param Dimension[] $dimensions
     * @param string $productType
     * @param array $entityIds
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function aroundGetQuery(
        \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice $subject,
        \Closure $proceed,
        array $dimensions,
        string $productType,
        array $entityIds = []
    ): Select {
        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->joinInner(
            ['cg' => $this->getTable('customer_group')],
            array_key_exists(CustomerGroupDimensionProvider::DIMENSION_NAME, $dimensions)
                ? sprintf(
                    '%s = %s',
                    $this->dimensionToFieldMapper[CustomerGroupDimensionProvider::DIMENSION_NAME],
                    $dimensions[CustomerGroupDimensionProvider::DIMENSION_NAME]->getValue()
                ) : '',
            ['customer_group_id']
        )->joinInner(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id',
            ['pw.website_id']
        )->joinInner(
            ['cwd' => $this->getTable('catalog_product_index_website')],
            'pw.website_id = cwd.website_id',
            []
        )->joinLeft(
            // we need this only for BCC in case someone expects table `tp` to be present in query
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND' .
            ' tp.customer_group_id = cg.customer_group_id AND tp.website_id = pw.website_id',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `Specific Customer Group`
            ['tier_price_1' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_1.' . $linkField . ' = e.' . $linkField . ' AND tier_price_1.all_groups = 0' .
            ' AND tier_price_1.customer_group_id = cg.customer_group_id AND tier_price_1.qty = 1' .
            ' AND tier_price_1.website_id = 0',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `Specific Website`
            //and Customer Group = `Specific Customer Group`
            ['tier_price_2' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_2.' . $linkField . ' = e.' . $linkField . ' AND tier_price_2.all_groups = 0 ' .
            'AND tier_price_2.customer_group_id = cg.customer_group_id AND tier_price_2.qty = 1' .
            ' AND tier_price_2.website_id = pw.website_id',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `ALL GROUPS`
            ['tier_price_3' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_3.' . $linkField . ' = e.' . $linkField . ' AND tier_price_3.all_groups = 1 ' .
            'AND tier_price_3.customer_group_id = 0 AND tier_price_3.qty = 1 AND tier_price_3.website_id = 0',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `Specific Website` and Customer Group = `ALL GROUPS`
            ['tier_price_4' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_4.' . $linkField . ' = e.' . $linkField . ' AND tier_price_4.all_groups = 1' .
            ' AND tier_price_4.customer_group_id = 0 AND tier_price_4.qty = 1' .
            ' AND tier_price_4.website_id = pw.website_id',
            []
        );

        foreach ($dimensions as $dimension) {
            if (!isset($this->dimensionToFieldMapper[$dimension->getName()])) {
                throw new \LogicException(
                    'Provided dimension is not valid for Price indexer: ' . $dimension->getName()
                );
            }
            $select->where($this->dimensionToFieldMapper[$dimension->getName()] . ' = ?', $dimension->getValue());
        }

        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->joinAttributeProcessor->process($select, 'tax_class_id');
        } else {
            $taxClassId = new \Zend_Db_Expr(0);
        }
        $select->columns(['tax_class_id' => $taxClassId]);

        /* ------------------RB Customization ---------------*/
        $select->joinLeft(
            ['rbvp' => $this->getTable('md_vendor_product_listing_idx')],
            'rbvp.marketplace_product_id = e.entity_id',
            ['vendor_id']
        );

        $price = $this->_addVendorAttributeToSelect(
            $select,
            'price',
            'e.entity_id'
        );

        $specialPrice = $this->_addVendorAttributeToSelect(
            $select,
            'special_price',
            'e.entity_id'
        );
        $specialFrom = $this->_addVendorAttributeToSelect(
            $select,
            'special_from_date',
            'e.entity_id'
        );
        $specialTo = $this->_addVendorAttributeToSelect(
            $select,
            'special_to_date',
            'e.entity_id'
        );

        /* ------------------RB Customization ---------------*/

        $this->joinAttributeProcessor->process($select, 'status', Status::STATUS_ENABLED);
        $currentDate = 'cwd.website_date';
        /* ------------------RB Customization ---------------*/
        /* $price = $this->joinAttributeProcessor->process($select, 'price');
        $specialPrice = $this->joinAttributeProcessor->process($select, 'special_price');
        $specialFrom = $this->joinAttributeProcessor->process($select, 'special_from_date');
        $specialTo = $this->joinAttributeProcessor->process($select, 'special_to_date'); */

        $maxUnsignedBigint = '~0';
        /* $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $specialFromExpr = "{$specialFrom} IS NULL OR {$specialFromDate} <= {$currentDate}";
        $specialToExpr = "{$specialTo} IS NULL OR {$specialToDate} >= {$currentDate}";
        $specialPriceExpr = $connection->getCheckSql(
            "{$specialPrice} IS NOT NULL AND ({$specialFromExpr}) AND ({$specialToExpr})",
            $specialPrice,
            $maxUnsignedBigint
        );*/
        $tierPrice = $this->getTotalTierPriceExpression($price);
        $tierPriceExpr = $connection->getIfNullSql($tierPrice, $maxUnsignedBigint);

        $specialFromDate = $connection->getDatePartSql($specialFrom);
        $specialToDate = $connection->getDatePartSql($specialTo);
        $tierPrice = $this->getTotalTierPriceExpression($price);
        $tierPriceExpr = $connection->getIfNullSql($tierPrice, $maxUnsignedBigint);
        $specialFromUse = $connection->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
        $specialToUse = $connection->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
        $specialFromHas = $connection->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
        $specialToHas = $connection->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
        $finalPrice = $connection->getCheckSql(
            "{$specialFromHas} > 0 AND {$specialToHas} > 0" . " AND {$specialPrice} < {$price}",
            $specialPrice,
            $price
        );
        /* $finalPrice = $connection->getLeastSql([
            new \Zend_Db_Expr('rbvps.price'),
            $specialPriceExpr,
            $tierPriceExpr,
        ]); */

        /* Core Magento Code */
        $priceMage = $this->joinAttributeProcessor->process($select, 'price');
        $specialPriceMage = $this->joinAttributeProcessor->process($select, 'special_price');
        $specialFromMage = $this->joinAttributeProcessor->process($select, 'special_from_date');
        $specialToMage = $this->joinAttributeProcessor->process($select, 'special_to_date');
        $specialFromDateMage = $connection->getDatePartSql($specialFromMage);
        $specialToDateMage = $connection->getDatePartSql($specialToMage);
        $specialFromExprMage = "{$specialFromMage} IS NULL OR {$specialFromDateMage} <= {$currentDate}";
        $specialToExprMage = "{$specialToMage} IS NULL OR {$specialToDateMage} >= {$currentDate}";
        $specialPriceExprMage = $connection->getCheckSql(
            "{$specialPriceMage} IS NOT NULL AND ({$specialFromExprMage}) AND ({$specialToExprMage})",
            $specialPriceMage,
            $maxUnsignedBigint
        );
        $tierPriceMage = $this->getTotalTierPriceExpression($priceMage);
        $tierPriceExprMage = $connection->getIfNullSql($tierPriceMage, $maxUnsignedBigint);
        $finalPriceMage = $connection->getLeastSql([
            $priceMage,
            $specialPriceExprMage,
            $tierPriceExprMage,
        ]);
        /* Core Magento Code */

        $select->columns(
            [
                //orig_price in catalog_product_index_price_final_tmp
                'price' => $connection->getIfNullSql($price, $connection->getIfNullSql($priceMage, 0)),
                //price in catalog_product_index_price_final_tmp
                'final_price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'min_price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'max_price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'tier_price' => $tierPrice,
            ]
        );

        $select->joinLeft(
            ['rbvps' => $this->getTable('md_vendor_product_listing_idx')],
            'rbvps.marketplace_product_id = e.entity_id',
            ['special_price','special_from_date','special_to_date']
        );
        $select->where("e.type_id = ?", $productType);

        if ($entityIds !== null) {
            if (count($entityIds) > 1) {
                $select->where(sprintf('e.entity_id BETWEEN %s AND %s', min($entityIds), max($entityIds)));
            } else {
                $select->where('e.entity_id = ?', $entityIds);
            }
        }

        /**
         * throw event for backward compatibility
         */
        $this->eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new ColumnValueExpression('e.entity_id'),
                'website_field' => new ColumnValueExpression('pw.website_id'),
                'store_field' => new ColumnValueExpression('cwd.default_store_id'),
            ]
        );

        return $select;
    }

    /**
     * Get total tier price expression
     *
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTotalTierPriceExpression(\Zend_Db_Expr $priceExpression)
    {
        $maxUnsignedBigint = '~0';

        return $this->getConnection()->getCheckSql(
            implode(
                ' AND ',
                [
                    'tier_price_1.value_id is NULL',
                    'tier_price_2.value_id is NULL',
                    'tier_price_3.value_id is NULL',
                    'tier_price_4.value_id is NULL'
                ]
            ),
            'NULL',
            $this->getConnection()->getLeastSql([
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_1', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_2', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_3', $priceExpression),
                    $maxUnsignedBigint
                ),
                $this->getConnection()->getIfNullSql(
                    $this->getTierPriceExpressionForTable('tier_price_4', $priceExpression),
                    $maxUnsignedBigint
                ),
            ])
        );
    }

    /**
     * Get tier price expression for table
     *
     * @param $tableAlias
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTierPriceExpressionForTable($tableAlias, \Zend_Db_Expr $priceExpression): \Zend_Db_Expr
    {
        return $this->getConnection()->getCheckSql(
            sprintf('%s.value = 0', $tableAlias),
            sprintf(
                'ROUND(%s * (1 - ROUND(%s.percentage_value * cwd.rate, 4) / 100), 4)',
                $priceExpression,
                $tableAlias
            ),
            sprintf('ROUND(%s.value * cwd.rate, 4)', $tableAlias)
        );
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

    /**
     * Add attribute join condition to select and return \Zend_Db_Expr
     * attribute value definition
     * If $condition is not empty apply limitation for select
     *
     * @param $select
     * @param $attrCode
     * @param $entity
     * @param $condition
     * @param bool $required
     * @return \Zend_Db_Expr
     */
    protected function _addVendorAttributeToSelect($select, $attrCode, $entity, $condition = null, $required = false)
    {
        $attributeTable = $this->getTable('md_vendor_product_listing_idx');
        $connection = $this->getConnection();
        $joinType = $condition !== null || $required ? 'join' : 'joinLeft';
        $productIdField = 'marketplace_product_id';

        if ($attrCode) {
            $alias = 'rbvp_' . $attrCode;
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = {$entity}",
                []
            );
            $expression = new \Zend_Db_Expr("{$alias}.{$attrCode}");
        }

        if ($condition !== null) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }
}
