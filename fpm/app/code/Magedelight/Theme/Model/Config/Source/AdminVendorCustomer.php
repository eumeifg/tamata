<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Theme
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Theme\Model\Config\Source;

class AdminVendorCustomer implements \Magedelight\Theme\Model\Config\Source\ArrayInterface
{
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $_options = [];
         $_options [] = ['value' => self::ADMIN_VALUE, 'label' => self::ADMIN_LABEL];
         $_options [] = ['value' => self::VENDOR_VALUE, 'label' => self::VENDOR_LABEL];
         $_options [] = ['value' => self::CUSTOMER_VALUE, 'label' => self::CUSTOMER_LABEL];
         
        return $_options;
    }
}
