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
namespace Magedelight\ProductAlert\Model\ResourceModel\Alert;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function getSelectCountSql()
    {
        $select = clone $this->getSelect();
        $select->reset(\Zend_Db_Select::ORDER);
        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    protected function _initSelect()
    {
        //   parent::_initSelect();
/*
        $this->addAttributeToSelect('name');
        $select = $this->getSelect();
        $productTable = Mage::getSingleton('core/resource')->getTableName(
            'catalog/product_entity'
        );
        $select->joinInner(
            ['s' => $productTable],
            'e.product_id = s.entity_id',
            ['cnt' => 'count(e.product_id)',
                'last_d' => 'MAX(add_date)',
                'first_d' => 'MIN(add_date)',
                'product_id',
                'website_id'])
            ->where('send_count=0')
            ->group(['e.website_id', 'e.product_id']);*/
        return $this;
    }
}
