<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class IncreaseOrderRafInvoicedAmount
 *
 * @package Aheadworks\Raf\Observer
 */
class IncreaseOrderRafInvoicedAmount implements ObserverInterface
{
    /**
     * Increase order aw_raf_invoiced attribute based on created invoice
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getBaseAwRafAmount()) {
            $order->setBaseAwRafInvoiced(
                $order->getBaseAwRafInvoiced() + $invoice->getBaseAwRafAmount()
            );
            $order->setAwRafInvoiced(
                $order->getAwRafInvoiced() + $invoice->getAwRafAmount()
            );
        }
        return $this;
    }
}
