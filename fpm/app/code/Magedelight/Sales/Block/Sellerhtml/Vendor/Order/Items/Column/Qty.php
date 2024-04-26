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
namespace Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Items\Column;

use Magento\Sales\Model\Order\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;

/**
 * Vendor Order items qty column renderer
 */
class Qty extends \Magedelight\Sales\Block\Sellerhtml\Vendor\Order\Items\Column\DefaultColumn
{
    /**
     * Get item
     *
     * @return Item|QuoteItem
     */
    public function getItem()
    {
        $item = $this->_getData('item');
        if ($item instanceof Item || $item instanceof QuoteItem) {
            return $item;
        } else {
            return $item->getOrderItem();
        }
    }
}
