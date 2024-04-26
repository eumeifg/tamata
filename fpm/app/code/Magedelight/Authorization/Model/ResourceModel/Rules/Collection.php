<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Authorization
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Authorization\Model\ResourceModel\Rules;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magedelight\Authorization\Model\Rules::class,
            \Magedelight\Authorization\Model\ResourceModel\Rules::class
        );
    }

    /**
     * Get rules by role id
     *
     * @param int $roleId
     * @return $this
     */
    public function getByRoles($roleId)
    {
        $this->addFieldToFilter('role_id', (int)$roleId);
        return $this;
    }

    /**
     * Sort by length
     *
     * @return $this
     */
    public function addSortByLength()
    {
        $length = $this->getConnection()->getLengthSql('{{resource_id}}');
        $this->addExpressionFieldToSelect('length', $length, 'resource_id');
        $this->getSelect()->order('length ' . \Magento\Framework\DB\Select::SQL_DESC);

        return $this;
    }
}
