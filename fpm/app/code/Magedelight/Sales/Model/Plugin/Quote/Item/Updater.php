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
namespace Magedelight\Sales\Model\Plugin\Quote\Item;

/**
 * Description of Updater
 *
 * @author Rocket Bazaar Core Team
 */
class Updater
{
    public function beforeUpdate(
        \Magento\Quote\Model\Quote\Item\Updater $subject,
        \Magento\Quote\Model\Quote\Item $item,
        array $info
    ) {
        $item->setVendorId($info['vendor_id']);
        return [$item, $info];
    }
}
