<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api\Data;

use Magento\Sales\Api\Data\CreditmemoInterface as SalesCreditmemoItemInterface;

/**
 * Interface CreditmemoItemInterface
 * @api
 */
interface CreditmemoItemInterface extends SalesCreditmemoItemInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const AW_RAF_AMOUNT = 'aw_raf_amount';
    const BASE_AW_RAF_AMOUNT = 'base_aw_raf_amount';
    /**#@-*/
}
