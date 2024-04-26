<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Model\ResourceModel\Core\Order\Item;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{

    /**
     * Set filter by vendor id
     *
     * @param mixed $vendor
     * @return $this
     */
    public function addVendorFilter($vendor = null)
    {
        if (is_array($vendor)) {
            $this->addFieldToFilter('vendor_id', ['in' => $vendor]);
        } elseif (empty(trim($vendor))) {
            return;
        } elseif ($vendor instanceof \Magedelight\Vendor\Model\Vendor) {
            $this->addFieldToFilter('vendor_id', $vendor->getVendorId());
        } else {
            $this->addFieldToFilter('vendor_id', $vendor);
        }
        return $this;
    }
}
