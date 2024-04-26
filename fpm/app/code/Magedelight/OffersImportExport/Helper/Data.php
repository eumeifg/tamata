<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_OffersImportExport
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\OffersImportExport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     *
     * @return array
     */
    public function getSampleData()
    {
        return [
            "marketplace_sku"=>"24-WG085",
            "vendor_sku"=>"VEN-24-WG085",
            "vendor_id"=>"10",
            "qty"=>"50",
            "price"=>"500",
            "special_price"=>"450",
            "special_from_date"=>"10/10/2017",
            "special_to_date"=>"20/10/2017",
            "reorder_level"=>"10",
            "status"=>"0",
        ];
    }
    
    /**
     *
     * @return array
     */
    public function getCSVFields()
    {
        return [
            "marketplace_sku"=>"marketplace_sku",
            "vendor_sku"=>"vendor_sku",
            "vendor_id"=>"vendor_id",
            "qty"=>"qty",
            "price"=>"price",
            "special_price"=>"special_price",
            "special_from_date"=>"special_from_date",
            "special_to_date"=>"special_to_date",
            "reorder_level"=>"reorder_level",
            "status"=>"status",
        ];
    }
    
    /**
     *
     * @return string
     */
    public function getTemplate()
    {
        return '"{{marketplace_sku}}","{{vendor_sku}}","{{vendor_id}}","{{qty}}","{{price}}"' .
            ',"{{special_price}}","{{special_from_date}}","{{special_to_date}}","{{reorder_level}}","{{status}}"';
    }
}
