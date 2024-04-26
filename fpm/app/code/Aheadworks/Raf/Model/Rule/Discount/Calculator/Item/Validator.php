<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;

/**
 * Class Validator
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\Item
 */
class Validator
{
    /**
     * Can apply discount on item
     *
     * @param AbstractItem $item
     * @param MetadataRule $metadataRule
     * @return bool
     */
    public function canApplyDiscount($item, $metadataRule)
    {
        return true;
    }
}
