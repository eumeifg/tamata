<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model\ResourceModel\Vendor\Grid;

use Magedelight\Vendor\Model\Source\Status as VendorStatus;

class ApprovedCollection extends AbstractCollection
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
        $this->addFieldToFilter('main_table.parent_vendor_id', ['null' => null]);
        $this->_addWebsiteIds(VendorStatus::VENDOR_STATUS_ACTIVE);
        $this->addFilterToMap('vendor_id', 'main_table.vendor_id');
        $this->addFieldToFilter('rvwd.status', VendorStatus::VENDOR_STATUS_ACTIVE);
        $this->addFieldToFilter('main_table.email', ['neq'=>'admin@gmail.com']);
        return $this;
    }
}
