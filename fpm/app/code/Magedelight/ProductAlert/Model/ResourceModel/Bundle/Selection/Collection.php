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
namespace Magedelight\ProductAlert\Model\ResourceModel\Bundle\Selection;

class Collection extends \Magento\Bundle\Model\ResourceModel\Selection\Collection
{
    public function _afterLoad()
    {
        parent::_afterLoad();

        if ($this->getStoreId() && $this->_items) {
            foreach ($this->_items as $item) {
                $item->setStoreId($this->getStoreId());

                /*show out of stock bundle options*/
                if (!$item->getData('is_salable')) {
                    $item->setData('is_salable', 1);
                    $item->setData('md_native_is_salable', 0);

                    $name = $item->getName();
                    $name .= ' (' . __('Out of Stock') . ')';
                    $item->setData('name', $name);
                } else {
                    $item->setData('md_native_is_salable', 1);
                }
            }
        }
        return $this;
    }

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

        return parent::load($printQuery, $logQuery);
    }
}
