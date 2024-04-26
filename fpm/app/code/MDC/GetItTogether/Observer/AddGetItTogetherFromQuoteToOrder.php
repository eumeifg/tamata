<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace MDC\GetItTogether\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MDC\GetItTogether\Api\Data\OrderGetItTogetherInterface;

class AddGetItTogetherFromQuoteToOrder implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        /** @var $order \Magento\Sales\Model\Order **/

        $quote = $observer->getEvent()->getQuote();
        /** @var $quote \Magento\Quote\Model\Quote **/

        $order->setData(
            OrderGetItTogetherInterface::COLUMN_NAME,
            $quote->getData(OrderGetItTogetherInterface::COLUMN_NAME)
        );
    }
}
