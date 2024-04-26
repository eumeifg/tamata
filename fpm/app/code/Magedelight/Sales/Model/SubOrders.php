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
namespace Magedelight\Sales\Model;

use Magedelight\Sales\Api\Data\SubOrdersInterface;
use Magedelight\Sales\Api\Data\VendorOrderInterface;

class SubOrders extends \Magento\Framework\DataObject implements SubOrdersInterface
{
    /**
     * Retrieve sub-orders of main order
     *
     * @return VendorOrderInterface[]
     */
    public function getSubOrders()
    {
        return $this->getData('sub_orders');
    }

    /**
     * @param VendorOrderInterface[] $subOrders
     * @return SubOrdersInterface|SubOrders
     */
    public function setSubOrders($subOrders)
    {
        return $this->setData('sub_orders', $subOrders);
    }
}
