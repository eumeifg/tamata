<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class RedeemForOrder
 *
 * @package Aheadworks\Raf\Observer
 */
class RedeemForOrder implements ObserverInterface
{
    /**
     *  {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var $order \Magento\Sales\Model\Order **/
        $order = $event->getOrder();
        /** @var $quote \Magento\Quote\Model\Quote $quote */
        $quote = $event->getQuote();

        if ($quote->getAwRafAmount()) {
            $order->setAwRafAmount($quote->getAwRafAmount());
            $order->setBaseAwRafAmount($quote->getBaseAwRafAmount());
            $order->setAwRafIsFriendDiscount($quote->getAwRafIsFriendDiscount());
            $order->setAwRafReferralLink($quote->getAwRafReferralLink());
            $order->setAwRafPercentAmount($quote->getAwRafPercentAmount());
            $order->setAwRafAmountType($quote->getAwRafAmountType());

            $order->setAwRafShippingPercent(
                $order->getExtensionAttributes()->getAwRafShippingPercent()
            );
            $order->setAwRafShippingAmount(
                $order->getExtensionAttributes()->getAwRafShippingAmount()
            );
            $order->setBaseAwRafShippingAmount(
                $order->getExtensionAttributes()->getBaseAwRafShippingAmount()
            );
        }
    }
}
