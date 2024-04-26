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
namespace Magedelight\Catalog\Helper\Bulkimport;

use Magento\Framework\App\Helper\AbstractHelper;

class Offers extends AbstractHelper
{

    public function getCSVHeaders()
    {
        return [
            'vendor_sku',
            'qty',
            'price',
            'special_price',
            'special_from_date',
            'special_to_date',
            'reorder_level'
        ];
    }
    
    public function getSampleRow()
    {
        return [
            "Enter Vendor Product Sku",
            "100",
            "1000",
            "800",
            null,
            null,
            '10'
        ];
    }
    
    public function getVitalFields()
    {
        return [
            "vendor_sku",
            "price",
            "qty"
        ];
    }
}
