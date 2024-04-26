<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Wishlist
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Wishlist\Helper\Plugin;

/**
 * Description of Data
 */
class Data
{
    public function beforeGetAddParams(\Magento\Wishlist\Helper\Data $subject, $item, array $params = [])
    {
        if ($item->getVendorId()) {
            $params['vendor_id'] = $item->getVendorId();
        }
        return [$item, $params];
    }
}
