<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Model\ResourceModel\User\Grid;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;
use Magedelight\Vendor\Model\ResourceModel\Vendor\Grid\AbstractCollection;

class VendorUsersCollection extends AbstractCollection
{
    
    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->_addWebsiteData(['*']);
        $this->getSelect()->joinLeft(
            ['mvwd' => 'md_vendor_website_data'],
            'main_table.parent_vendor_id = mvwd.vendor_id',
            ['business_name']
        );
        $this->addFieldToFilter('main_table.parent_vendor_id', ['neq' => null]);
        $this->addFilterToMap('email', 'main_table.email');
        $this->addFilterToMap('name', 'rvwd.name');
        $this->addFilterToMap('business_name', 'mvwd.business_name');
        $this->addFilterToMap('vendor_id', 'main_table.vendor_id');
        $this->addFilterToMap('status', 'rvwd.status');
        $this->addFieldToFilter('main_table.email', ['neq'=>'admin@gmail.com']);
        return $this;
    }
}
