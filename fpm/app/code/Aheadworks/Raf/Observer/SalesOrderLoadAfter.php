<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

/**
 * Class SalesOrderLoadAfter
 *
 * @package Aheadworks\Raf\Observer
 */
class SalesOrderLoadAfter implements ObserverInterface
{
    /**
     * Set forced canCreditmemo flag
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() || $order->getState() === Order::STATE_CLOSED) {
            return $this;
        }

        if ((abs($order->getAwRafInvoiced()) - abs($order->getAwRafRefunded())) > 0) {
            $order->setForcedCanCreditmemo(true);
        }

        return $this;
    }
}
