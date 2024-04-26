<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Quote;

use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\Order\Item;

/**
 * Class ConvertQuoteItemToOrderItemPlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\Quote
 */
class ConvertQuoteItemToOrderItemPlugin
{
    /**
     * @param ToOrderItem $subject
     * @param \Closure $proceed
     * @param AbstractItem $item
     * @param array $additional
     * @return Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(
        ToOrderItem $subject,
        \Closure $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);

        $orderItem->setAwRafPercent($item->getAwRafPercent());
        $orderItem->setBaseAwRafAmount($item->getBaseAwRafAmount());
        $orderItem->setAwRafAmount($item->getAwRafAmount());
        $orderItem->setAwRafRuleIds($item->getAwRafRuleIds());

        return $orderItem;
    }
}
