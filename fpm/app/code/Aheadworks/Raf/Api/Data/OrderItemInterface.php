<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api\Data;

use Magento\Sales\Api\Data\OrderItemInterface as SalesOrderItemInterface;

/**
 * Interface OrderItemInterface
 * @api
 */
interface OrderItemInterface extends SalesOrderItemInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const AW_RAF_RULE_IDS = 'aw_raf_rule_ids';
    const AW_RAF_PERCENT = 'aw_raf_percent';
    const AW_RAF_AMOUNT = 'aw_raf_amount';
    const BASE_AW_RAF_AMOUNT = 'base_aw_raf_amount';
    const AW_RAF_INVOICED = 'aw_raf_invoiced';
    const BASE_AW_RAF_INVOICED = 'base_aw_raf_invoiced';
    const AW_RAF_REFUNDED = 'aw_raf_refunded';
    const BASE_AW_RAF_REFUNDED = 'base_aw_raf_refunded';
    /**#@-*/
}
