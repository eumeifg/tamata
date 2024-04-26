<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Quote\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Class DiscountPlugin
 *
 * @package Aheadworks\Raf\Plugin\Model\Quote\Total
 */
class DiscountPlugin extends AbstractResetTotalPlugin
{
    /**
     * @inheritdoc
     */
    protected function updateBeforeReset(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Total $total)
    {
        if ($quote->getCouponCode()) {
            $this->discountUsed = true;
            foreach ($shippingAssignment->getItems() as $item) {
                $item->setNoDiscount(1);
            }
        }
        return true;
    }
}
