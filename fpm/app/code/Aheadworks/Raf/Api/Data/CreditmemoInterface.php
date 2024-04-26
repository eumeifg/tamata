<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Api\Data;

use Magento\Sales\Api\Data\CreditmemoInterface as SalesCreditmemoInterface;

/**
 * Interface CreditmemoInterface
 * @api
 */
interface CreditmemoInterface extends SalesCreditmemoInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const AW_RAF_AMOUNT = 'aw_raf_amount';
    const BASE_AW_RAF_AMOUNT = 'base_aw_raf_amount';
    const AW_RAF_IS_RETURN_TO_ACCOUNT = 'aw_raf_is_return_to_account';
    /**#@-*/
}
