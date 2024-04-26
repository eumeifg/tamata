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
namespace Magedelight\Catalog\Model\Method;

class Bestselling extends AbstractIndexMethod
{
    const CODE = 'bestsellers';

    const NAME = 'Best Sellers';

    /**
     * @var bool
     */
    protected $isAllGrouped = false;

    /**
     * @return \Zend_Db_Expr
     */
    public function getColumnSelect()
    {
        $sql = ' SELECT SUM(order_item.qty_ordered)'
            . ' FROM ' . $this->_resource->getTableName(
                'sales_order_item'
            )
            . ' AS order_item';

        // exclude items (products)
        $exclude = $this->_scopeConfig->getValue(
            'rbsort/bestsellers/exclude',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($exclude) {
            $sql .= ' LEFT JOIN ' . $this->_resource
                    ->getTableName('sales_order')
                . ' AS flat_orders'
                . '   ON (flat_orders.entity_id = order_item.order_id) ';
        }

        if ($this->isAllGrouped) {
            $sql .= ' INNER JOIN ' . $this->_resource
                    ->getTableName('quote_item_option')
                . ' AS order_item_option'
                . '   ON (order_item.item_id = order_item_option.item_id AND order_item_option.code="product_type")'
                . ' WHERE order_item_option.product_id = e.entity_id ';
        } else {
            $sql .= ' WHERE order_item.product_id = e.entity_id ';
        }

        if ($exclude) {
            $temp = explode(',', $exclude);
            $exclude = implode('\',\'', $temp);
            $sql .= ' AND flat_orders.status NOT IN (\'' . $exclude . '\')';
        }

        $sql .= $this->_indexHelper->getPeriodCondition(
            'order_item.created_at',
            'bestsellers/best_period'
        );
        $sql .= $this->_indexHelper->getStoreCondition('order_item.store_id');

        return new \Zend_Db_Expr('(' . $sql . ')');
    }
}
