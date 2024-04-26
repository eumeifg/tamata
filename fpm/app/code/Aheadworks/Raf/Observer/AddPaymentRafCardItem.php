<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Observer;

use Aheadworks\Raf\Api\Data\TotalsInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\Cart;

/**
 * Class AddPaymentRafCardItem
 *
 * @package Aheadworks\Raf\Observer
 */
class AddPaymentRafCardItem implements ObserverInterface
{
    /**
     * Merge RAF amount into discount of payment checkout totals
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $salesEntity = $cart->getSalesModel();
        $value = abs($salesEntity->getDataUsingMethod(TotalsInterface::BASE_AW_RAF_AMOUNT));
        if ($value > 0.0001) {
            $cart->addDiscount((double)$value);
        }
    }
}
