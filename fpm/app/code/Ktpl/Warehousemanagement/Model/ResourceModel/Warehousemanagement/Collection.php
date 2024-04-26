<?php
/**
 * A Magento 2 module that functions for Warehouse management
 * Copyright (C) 2019
 *
 * This file included in Ktpl/Warehousemanagement is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'warehousemanagement_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ktpl\Warehousemanagement\Model\Warehousemanagement::class,
            \Ktpl\Warehousemanagement\Model\ResourceModel\Warehousemanagement::class
        );
    }
    protected function _initSelect()
    {
        parent::_initSelect();
    }
}
