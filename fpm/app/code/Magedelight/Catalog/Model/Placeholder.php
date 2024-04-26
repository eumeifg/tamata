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
namespace Magedelight\Catalog\Model;

class Placeholder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Magedelight\Catalog\Model\ResourceModel\Placeholder::class);
    }

    /**
     * Retrieve store placeholders by given attribute id.
     *
     * @param int $attributeId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStorePlaceholdersByAttributeId($attributeId)
    {
        $connection = $this->_getResource()->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            ['main_table' => $this->_getResource()->getMainTable()],
            ['store_id', 'value']
        )->join(
            ['eav_attr' => $this->_getResource()->getTable('eav_attribute')],
            'eav_attr.attribute_id = main_table.attribute_id'
        )
        ->where(
            'main_table.attribute_id = :attribute_id'
        );

        return $connection->fetchPairs($select, $bind);
    }
}
