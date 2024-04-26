<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Quote;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Quote\Model\Quote\Address\ToOrder;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class ConvertQuoteAddressToOrderPlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\Quote
 */
class ConvertQuoteAddressToOrderPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param ToOrder $subject
     * @param \Closure $proceed
     * @param Address $quoteAddress
     * @param array $data
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        ToOrder $subject,
        \Closure $proceed,
        Address $quoteAddress,
        $data = []
    ) {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $proceed($quoteAddress, $data);

        $extensionAttributes = $order->getExtensionAttributes()
            ? $order->getExtensionAttributes()
            : $this->orderExtensionFactory->create();

        $extensionAttributes->setAwRafShippingPercent($quoteAddress->getAwRafShippingPercent());
        $extensionAttributes->setAwRafShippingAmount($quoteAddress->getAwRafShippingAmount());
        $extensionAttributes->setBaseAwRafShippingAmount($quoteAddress->getBaseAwRafShippingAmount());

        $order->setExtensionAttributes($extensionAttributes);
        return $order;
    }
}
