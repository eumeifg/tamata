<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount;

use Aheadworks\Raf\Model\Metadata\Rule\Discount as MetadataRuleDiscount;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class ItemsApplier
 *
 * @package Aheadworks\Raf\Model\Rule\Discount
 */
class ItemsApplier
{
    /**
     * Apply rule discount by items
     *
     * @param CartItemInterface[]|AbstractItem[] $items
     * @param MetadataRuleDiscount $metadataRuleDiscount
     * @return void
     */
    public function apply($items, $metadataRuleDiscount)
    {
        foreach ($items as $item) {
            // To determine the child item discount, we calculate the parent
            if ($item->getParentItem()) {
                continue;
            }
            $item->setAwRafAmount(0);
            $item->setBaseAwRafAmount(0);
            $this->processApply($item, $metadataRuleDiscount);
        }
    }

    /**
     * Apply rule discount by items
     *
     * @param CartItemInterface|AbstractItem $item
     * @param MetadataRuleDiscount $metadataRuleDiscount
     * @return $this
     */
    private function processApply($item, $metadataRuleDiscount)
    {
        if ($itemDiscount = $this->getItemDiscountById($item->getId(), $metadataRuleDiscount->getItems())) {
            $item->setAwRafPercent($itemDiscount->getPercent());
            $item->setAwRafAmount($itemDiscount->getAmount());
            $item->setBaseAwRafAmount($itemDiscount->getBaseAmount());
            $item->setAwRafRuleIds($itemDiscount->getRuleId());

            $address = $item->getAddress();
            $address->setAwRafRuleIds($itemDiscount->getRuleId());

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    if ($childDiscount = $this->getItemDiscountById($child->getId(), $itemDiscount->getChildren())) {
                        $child->setAwRafAmount($childDiscount->getAmount());
                        $child->setBaseAwRafAmount($childDiscount->getBaseAmount());
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Retrieve item discount by id
     *
     * @param int $id
     * @param MetadataRuleDiscount\Item[] $items
     * @return MetadataRuleDiscount\Item|bool
     */
    private function getItemDiscountById($id, $items)
    {
        foreach ($items as $item) {
            if ($item->getItem()->getId() == $id) {
                return $item;
            }
        }

        return false;
    }
}
