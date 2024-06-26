<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent;

use Aheadworks\Raf\Model\Metadata\Rule\Discount\ItemFactory as MetadataRuleDiscountItemFactory;
use Aheadworks\Raf\Model\Metadata\Rule\Discount\Item as MetadataRuleDiscountItem;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Processor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Validator;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\Item\Distributor;
use Aheadworks\Raf\Model\Rule\Discount\Calculator\ItemsCalculatorInterface;

/**
 * Class Items
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator\ByPercent
 */
class Items implements ItemsCalculatorInterface
{
    /**
     * @var MetadataRuleDiscountItemFactory
     */
    private $metadataRuleDiscountItemFactory;

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
     * @param MetadataRuleDiscountItemFactory $metadataRuleDiscountItemFactory
     * @param Validator $validator
     * @param Processor $processor
     * @param Distributor $distributor
     */
    public function __construct(
        MetadataRuleDiscountItemFactory $metadataRuleDiscountItemFactory,
        Validator $validator,
        Processor $processor,
        Distributor $distributor
    ) {
        $this->metadataRuleDiscountItemFactory = $metadataRuleDiscountItemFactory;
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
        foreach ($items as $item) {
            if ($item->getParentItem() || !$this->validator->canApplyDiscount($item, $metadataRule)) {
                continue;
            }

            $itemPrice = $this->processor->getTotalItemPrice($item);
            $baseItemPrice = $this->processor->getTotalItemBasePrice($item);
            $rulePercent = $metadataRule->getDiscountAmount();
            $rulePrc = $rulePercent / 100;

            /** @var MetadataRuleDiscountItem $metadataRuleDiscountItem */
            $metadataRuleDiscountItem = $this->metadataRuleDiscountItemFactory->create();
            $metadataRuleDiscountItem
                ->setPercent($rulePercent)
                ->setAmount($itemPrice * $rulePrc)
                ->setBaseAmount($baseItemPrice * $rulePrc)
                ->setItem($item)
                ->setRuleId($metadataRule->getId());

            $itemsDiscount[] = $this->distributor->distribute($metadataRuleDiscountItem);
        }

        return $itemsDiscount;
    }
}
