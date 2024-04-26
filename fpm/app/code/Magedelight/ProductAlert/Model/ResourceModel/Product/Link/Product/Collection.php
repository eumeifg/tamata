<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_ProductAlert
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\ProductAlert\Model\ResourceModel\Product\Link\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
{
    /**
     * @param bool|false $printQuery
     * @param bool|false $logQuery
     * @return $this
     * @throws \Zend_Db_Select_Exception
     */
    public function load($printQuery = false, $logQuery = false)
    {
        /*remove in stock filter*/
        $select = $this->getSelect();
        $where = $select->getPart('where');
        foreach ($where as $i => $item) {
            if (strpos($item, 'stock_status') !== false) {
                unset($where[$i]);
            }
        }
        $select->setPart('where', $where);

        $from = $select->getPart('from');
        if (array_key_exists('at_inventory_in_stock', $from)
        ) {
            $from['at_inventory_in_stock']['joinCondition'] =
                str_replace(
                    'AND at_inventory_in_stock.is_in_stock=1',
                    '',
                    $from['at_inventory_in_stock']['joinCondition']
                );
        }
        $select->setPart('from', $from);

        return parent::load($printQuery, $logQuery);
    }
}
