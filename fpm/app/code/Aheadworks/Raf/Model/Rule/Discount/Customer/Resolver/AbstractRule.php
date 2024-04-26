<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Rule\Discount\Customer\Resolver;

use Aheadworks\Raf\Model\Metadata\Rule as RuleMetadata;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Rule
 *
 * @package Aheadworks\Raf\Model\Rule\Discount\Customer\Resolver
 */
abstract class AbstractRule
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Resolve rule
     *
     * @param Quote $quote
     * @param AddressInterface $address
     * @return RuleMetadata
     */
    public function resolve($quote, $address)
    {
        $ruleData = $this->prepareData($quote, $address);

        return $this->objectManager->create(RuleMetadata::class, ['data' => $ruleData]);
    }

    /**
     * Prepare rule data
     *
     * @param Quote $quote
     * @param AddressInterface $address
     * @return array
     */
    abstract protected function prepareData($quote, $address);
}
