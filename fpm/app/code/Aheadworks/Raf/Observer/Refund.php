<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Observer;

use Magento\Framework\Event\ObserverInterface;
use Aheadworks\Raf\Model\Config;
use Magento\Framework\Event\Observer;

/**
 * Class Refund
 *
 * @package Aheadworks\Raf\Observer
 */
class Refund implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Set refund amount to creditmemo
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();

        // If refund RAF amount
        if ($creditmemo->getBaseAwRafAmount()) {
            $order->setBaseAwRafRefunded($order->getBaseAwRafRefunded() + $creditmemo->getBaseAwRafAmount());
            $order->setAwRafRefunded($order->getAwRafRefunded() + $creditmemo->getAwRafAmount());

            /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
            foreach ($creditmemo->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }

                $orderItem->setAwRafRefunded($orderItem->getAwRafRefunded() + $item->getAwRafAmount());
                $orderItem->setBaseAwRafRefunded($orderItem->getBaseAwRafRefunded() + $item->getBaseAwRafAmount());
            }

            // we need to update flag after credit memo was refunded and order's properties changed
            if ($order->getAwRafInvoiced() < 0
                && $order->getAwRafInvoiced() == $order->getAwRafRefunded()
            ) {
                $order->setForcedCanCreditmemo(false);
            }
        }

        return $this;
    }
}
