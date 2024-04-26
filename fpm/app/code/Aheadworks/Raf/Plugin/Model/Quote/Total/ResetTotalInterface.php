<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Model\Quote\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Interface ResetTotalInterface for plugins used to reset totals
 *
 * @package Aheadworks\Raf\Plugin\Model\Quote\Total
 */
interface ResetTotalInterface
{
    /**
     * Before collect method.
     *
     * Reset items for $shippingAssignment.
     * So in many cases it prevent total to be processed
     *
     * @param AbstractTotal $subject
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return array
     */
    public function beforeCollect(
        AbstractTotal $subject,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    );

    /**
     * After collect method.
     *
     * Restore items for $shippingAssignment.
     * It is required to restore items for other
     * totals processing
     *
     * @param AbstractTotal $subject
     * @param AbstractTotal $result
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return array
     */
    public function afterCollect(
        AbstractTotal $subject,
        AbstractTotal $result,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    );
}
