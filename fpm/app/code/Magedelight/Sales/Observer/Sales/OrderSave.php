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
namespace Magedelight\Sales\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use Magedelight\Sales\Model\Order\SplitOrder;

class OrderSave implements ObserverInterface
{
    /**
     * @var SplitOrder
     */
    protected $splitOrder;

    /**
     * OrderSave constructor.
     * @param SplitOrder $splitOrder
     */
    public function __construct(
        SplitOrder $splitOrder
    ) {
        $this->splitOrder = $splitOrder;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        if (!$order->getData('is_split')) {
            $this->splitOrder->execute($order);
        }
    }
}
