<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed;

use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Processor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Validator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Distributor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ItemsCalculatorInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed\Items\Item as CartByFixedItemCalculator;
use Aheadworks\Raf\Model\Metadata\Rule as MetadataRule;

/**
 * Class Items
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\CartByFixed
 */
class Items implements ItemsCalculatorInterface
{
    /**
     * @var CartByFixedItemCalculator
     */
    private $cartByFixedItemCalculator;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var Distributor
     */
    private $distributor;

    /**
     * @param CartByFixedItemCalculator $cartByFixedItemCalculator
     * @param Validator $validator
     * @param Processor $processor
     * @param Distributor $distributor
     */
    public function __construct(
        CartByFixedItemCalculator $cartByFixedItemCalculator,
        Validator $validator,
        Processor $processor,
        Distributor $distributor
    ) {
        $this->cartByFixedItemCalculator = $cartByFixedItemCalculator;
        $this->validator = $validator;
        $this->processor = $processor;
        $this->distributor = $distributor;
    }

    /**
     * {@inheritdoc}
     */
    public function calculate($items, $metadataRule)
    {
        $itemsDiscount = [];
        $validItems = $this->getValidItems($items, $metadataRule);
        $this->cartByFixedItemCalculator->init($validItems, $metadataRule->getDiscountAmount());
        foreach ($validItems as $item) {
            $metadataRuleDiscountItem = $this->cartByFixedItemCalculator->calculateItemAmount($item);
            $metadataRuleDiscountItem->setRuleId($metadataRule->getId());
            $itemsDiscount[] = $this->distributor->distribute($metadataRuleDiscountItem);
        }

        return $itemsDiscount;
    }

    /**
     * @param AbstractItem[] $items
     * @param MetadataRule $metadataRule
     * @return AbstractItem[]
     */
    private function getValidItems($items, $metadataRule)
    {
        $validItems = [];
        foreach ($items as $item) {
            if ($item->getParentItem()
                || !$this->validator->canApplyDiscount($item, $metadataRule)
                || !($this->processor->getTotalItemPrice($item) > 0)
            ) {
                continue;
            }
            $validItems[] = $item;
        }

        return $validItems;
    }
}
