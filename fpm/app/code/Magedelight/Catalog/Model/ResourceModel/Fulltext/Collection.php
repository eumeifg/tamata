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
namespace Magedelight\Catalog\Model\ResourceModel\Fulltext;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{

    /**
     * Join Product Price Table with left-join possibility
     *
     * @param bool $joinLeft
     * @return $this
     * @throws \Zend_Db_Exception
     * @throws \Zend_Db_Select_Exception
     * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::_productLimitationJoinPrice()
     */
    protected function _productLimitationPrice($joinLeft = false)
    {
        $filters = $this->_productLimitationFilters;
        if (!$filters->isUsingPriceIndex()) {
            return $this;
        }

        // Preventing overriding price loaded from EAV because we want to use the one from index
        $this->removeAttributeToSelect('price');

        $connection = $this->getConnection();
        $select = $this->getSelect();
        $joinCond = join(
            ' AND ',
            [
                'price_index.entity_id = e.entity_id',
                $connection->quoteInto('price_index.website_id = ?', $filters['website_id']),
                $connection->quoteInto('price_index.customer_group_id = ?', $filters['customer_group_id'])
            ]
        );

        $fromPart = $select->getPart(\Magento\Framework\DB\Select::FROM);
        if (!isset($fromPart['price_index'])) {
            $least = $connection->getLeastSql(['price_index.min_price', 'price_index.tier_price']);
            $minimalExpr = $connection->getCheckSql(
                'price_index.tier_price IS NOT NULL',
                $least,
                'price_index.min_price'
            );
            $colls = [
                'price',
                'tax_class_id',
                'final_price',
                'minimal_price' => $minimalExpr,
                'min_price',
                'max_price',
                'tier_price',
                'vendor_special_price' => 'special_price',
                'vendor_special_from_date' => 'special_from_date',
                'vendor_special_to_date' => 'special_to_date',
            ];
            $tableName = ['price_index' => $this->getTable('catalog_product_index_price')];
            if ($joinLeft) {
                $select->joinLeft($tableName, $joinCond, $colls);
            } else {
                $select->join($tableName, $joinCond, $colls);
            }

            // Set additional field filters
            foreach ($this->_priceDataFieldFilters as $filterData) {
                $select->where(call_user_func_array('sprintf', $filterData));
            }
        } else {
            $fromPart['price_index']['joinCondition'] = $joinCond;
            $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
        }
        //Clean duplicated fields
        $this->_resourceHelper->prepareColumnsList($select);

        return $this;
    }
}
