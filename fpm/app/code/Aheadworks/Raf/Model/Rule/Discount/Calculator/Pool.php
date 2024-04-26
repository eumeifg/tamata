<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Calculator;

/**
 * Class Pool
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Calculator
 */
class Pool
{
    /**
     * @var array
     */
    private $calculators;

    /**
     * @param array $calculators
     */
    public function __construct(
        array $calculators = []
    ) {
        $this->calculators = $calculators;
    }

    /**
     * Retrieve calculator by type
     *
     * @param string $type
     * @return DiscountCalculatorInterface
     * @throws \InvalidArgumentException
     */
    public function getCalculatorByType($type)
    {
        if (!isset($this->calculators[$type])) {
            throw new \InvalidArgumentException($type . ' is unknown type');
        }

        return $this->calculators[$type];
    }
}
