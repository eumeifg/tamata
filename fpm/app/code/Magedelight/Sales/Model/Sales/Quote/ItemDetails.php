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
namespace Magedelight\Sales\Model\Sales\Quote;

class ItemDetails extends \Magento\Tax\Model\Sales\Quote\ItemDetails
{
    const KEY_VENDOR_ID = 'vendor_id';

    /*
     * Get Vendor Id from Quote Item details object.
     */
    public function getVendorId()
    {
        return $this->getData(self::KEY_VENDOR_ID);
    }

    /*
     * Set Vendor Id to Quote Item details object.
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(self::KEY_VENDOR_ID, $vendorId);
    }
}
