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

class DefaultPrice extends \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
{

    /**
     * Forms Select for collecting price related data for final price index table
     * Next types of prices took into account: default, special, tier price
     * Moved to protected for possible reusing
     *
     * @param int|array $entityIds Ids for filtering output result
     * @param string|null $type Type for filtering output result by specified product type (all if null)
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 101.0.8
     */
    protected function getSelect($entityIds = null, $type = null)
    {
        $metadata = $this->getMetadataPool()->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        )->join(
            ['cw' => $this->getTable('store_website')],
            '',
            ['website_id']
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->join(
            ['csg' => $this->getTable('store_group')],
            'csg.website_id = cw.website_id AND cw.default_group_id = csg.group_id',
            []
        )->join(
            ['cs' => $this->getTable('store')],
            'csg.default_store_id = cs.store_id AND cs.store_id != 0',
            []
        )->join(
            ['pw' => $this->getTable('catalog_product_website')],
            'pw.product_id = e.entity_id AND pw.website_id = cw.website_id',
            []
        )->joinLeft(
            // we need this only for BCC in case someone expects table `tp` to be present in query
            ['tp' => $this->getTable('catalog_product_index_tier_price')],
            'tp.entity_id = e.entity_id AND tp.customer_group_id = cg.customer_group_id' .
            ' AND tp.website_id = pw.website_id',
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
            'tier_price_2.' . $linkField . ' = e.' . $linkField . ' AND tier_price_2.all_groups = 0' .
            ' AND tier_price_2.customer_group_id = cg.customer_group_id AND tier_price_2.qty = 1' .
            ' AND tier_price_2.website_id = cw.website_id',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `All Websites` and Customer Group = `ALL GROUPS`
            ['tier_price_3' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_3.' . $linkField . ' = e.' . $linkField . ' AND tier_price_3.all_groups = 1' .
            ' AND tier_price_3.customer_group_id = 0 AND tier_price_3.qty = 1 AND tier_price_3.website_id = 0',
            []
        )->joinLeft(
            // calculate tier price specified as Website = `Specific Website` and Customer Group = `ALL GROUPS`
            ['tier_price_4' => $this->getTable('catalog_product_entity_tier_price')],
            'tier_price_4.' . $linkField . ' = e.' . $linkField . ' AND tier_price_4.all_groups = 1' .
            ' AND tier_price_4.customer_group_id = 0 AND tier_price_4.qty = 1' .
            ' AND tier_price_4.website_id = cw.website_id',
            []
        );

        if ($type !== null) {
            $select->where('e.type_id = ?', $type);
        }

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $this->_addAttributeToSelect(
            $select,
            'status',
            'e.' . $linkField,
            'cs.store_id',
            $statusCond,
            true
        );
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect(
                $select,
                'tax_class_id',
                'e.' . $linkField,
                'cs.store_id'
            );
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }
        $select->columns(['tax_class_id' => $taxClassId]);
        
        /* ------------------RB Customization ---------------*/
        $select ->joinLeft(
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

        $priceMage = $this->_addAttributeToSelect(
            $select,
            'price',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialPriceMage = $this->_addAttributeToSelect(
            $select,
            'special_price',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialFromMage = $this->_addAttributeToSelect(
            $select,
            'special_from_date',
            'e.' . $linkField,
            'cs.store_id'
        );
        $specialToMage = $this->_addAttributeToSelect(
            $select,
            'special_to_date',
            'e.' . $linkField,
            'cs.store_id'
        );
        $currentDate = 'cwd.website_date';
            
        $maxUnsignedBigint = '~0';
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
        
        /* ------------------RB Customization ---------------*/
       
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

        $select->columns(
            [
                'orig_price' => $connection->getIfNullSql($price, $connection->getIfNullSql($priceMage, 0)),
                'price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'min_price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'max_price' => $connection->getIfNullSql($finalPrice, $connection->getIfNullSql($finalPriceMage, 0)),
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
                'special_price' => new \Zend_Db_Expr('rbvp.special_price'),
                'special_from_date' => new \Zend_Db_Expr('rbvp.special_from_date'),
                'special_to_date' => new \Zend_Db_Expr('rbvp.special_to_date')
            ]
        );
        /* ------------------RB Customization ---------------*/

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        
        $select->group(['e.entity_id', 'cg.customer_group_id', 'cw.website_id']);

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'prepare_catalog_product_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id'),
            ]
        );

        return $select;
    }
    
    /**
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
     * @param string $tableAlias
     * @param \Zend_Db_Expr $priceExpression
     * @return \Zend_Db_Expr
     */
    private function getTierPriceExpressionForTable($tableAlias, \Zend_Db_Expr $priceExpression)
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
     * Add attribute join condition to select and return \Zend_Db_Expr
     * attribute value definition
     * If $condition is not empty apply limitation for select
     *
     * @param type $select
     * @param type $attrCode
     * @param type $entity
     * @param type $condition
     * @param type $required
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
    
    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @param int[]|null $entityIds
     * @return $this
     */
    protected function _movePriceDataToIndexTable($entityIds = null)
    {
        $columns = [
            'entity_id' => 'entity_id',
            'customer_group_id' => 'customer_group_id',
            'website_id' => 'website_id',
            'tax_class_id' => 'tax_class_id',
            'vendor_id' => 'vendor_id',
            'price' => 'orig_price',
            'final_price' => 'price',
            'min_price' => 'min_price',
            'max_price' => 'max_price',
            'tier_price' => 'tier_price',
            'special_price' => 'special_price',
            'special_from_date' => 'special_from_date',
            'special_to_date' => 'special_to_date'
        ];

        $connection = $this->getConnection();
        $table = $this->_getDefaultFinalPriceTable();
        $select = $connection->select()->from($table, $columns);

        if ($entityIds !== null) {
            $select->where('entity_id in (?)', count($entityIds) > 0 ? $entityIds : 0);
        }
        $select->group(['entity_id', 'customer_group_id', 'website_id']);

        $query = $select->insertFromSelect($this->getIdxTable(), [], false);
        $connection->query($query);

        $connection->delete($table);

        return $this;
    }
    
    /**
     * Create (if needed), clean and return structure of final price table
     *
     * @return IndexTableStructure
     */
    private function prepareFinalPriceTable()
    {
        $tableName = $this->_getDefaultFinalPriceTable();
        $this->getConnection()->delete($tableName);

        $finalPriceTable = $this->indexTableStructureFactory->create([
            'tableName' => $tableName,
            'entityField' => 'entity_id',
            'customerGroupField' => 'customer_group_id',
            'websiteField' => 'website_id',
            'taxClassField' => 'tax_class_id',
            'vendorField' => 'vendor_id',
            'originalPriceField' => 'orig_price',
            'finalPriceField' => 'price',
            'minPriceField' => 'min_price',
            'maxPriceField' => 'max_price',
            'tierPriceField' => 'tier_price',
            'specialPriceField' => 'special_price',
            'specialFromDateField' => 'special_from_date',
            'specialToDateField' => 'special_to_date',
        ]);

        return $finalPriceTable;
    }
}
