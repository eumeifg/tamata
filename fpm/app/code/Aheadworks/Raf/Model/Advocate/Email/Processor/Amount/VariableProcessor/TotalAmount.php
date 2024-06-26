<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Email\Processor\Amount\VariableProcessor;

use Aheadworks\Raf\Api\Data\AdvocateSummaryInterface;
use Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer\PriceFormatResolver;
use Aheadworks\Raf\Model\Advocate\Email\Processor\VariableProcessor\VariableProcessorInterface;
use Aheadworks\Raf\Model\Source\Customer\Advocate\Email\BaseAmountVariables;
use Aheadworks\Raf\Model\Source\Rule\AdvocateOffType;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class TotalAmount
 *
 * @package Aheadworks\Raf\Model\Advocate\Email\Processor\Amount\VariableProcessor
 */
class TotalAmount implements VariableProcessorInterface
{
    /**
     * @var PriceFormatResolver
     */
    private $priceFormatResolver;

    /**
     * @param PriceFormatResolver $priceFormatResolver
     */
    public function __construct(
        PriceFormatResolver $priceFormatResolver
    ) {
        $this->priceFormatResolver = $priceFormatResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareVariables($variables)
    {
        /** @var AdvocateSummaryInterface $advocateSummary */
        $advocateSummary = $variables[BaseAmountVariables::ADVOCATE_SUMMARY];
        /** @var StoreInterface $store */
        $store = $variables[BaseAmountVariables::STORE];

        $variables[BaseAmountVariables::TOTAL_AMOUNT_FORMATTED] = $this->priceFormatResolver->resolve(
            $advocateSummary->getCumulativeAmount(),
            AdvocateOffType::FIXED,
            $store->getId()
        );

        return $variables;
    }
}
