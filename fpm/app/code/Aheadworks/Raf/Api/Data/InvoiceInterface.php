<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api\Data;

use Magento\Sales\Api\Data\InvoiceInterface as SalesInvoiceInterface;

/**
 * Interface InvoiceInterface
 * @api
 */
interface InvoiceInterface extends SalesInvoiceInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const AW_RAF_AMOUNT = 'aw_raf_amount';
    const BASE_AW_RAF_AMOUNT = 'base_aw_raf_amount';
    /**#@-*/
}
