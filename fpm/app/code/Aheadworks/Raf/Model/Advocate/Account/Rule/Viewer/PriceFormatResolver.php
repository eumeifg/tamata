<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate\Account\Rule\Viewer;

use Aheadworks\Raf\Model\Advocate\PriceFormatter;
use Aheadworks\Raf\Model\Source\Rule\BaseOffType;

/**
 * Class Viewer
 * @package Aheadworks\Raf\Model\Advocate\Account\Rule
 */
class PriceFormatResolver
{
    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        PriceFormatter $priceFormatter
    ) {
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * Resolve price and format
     *
     * @param float $price
     * @param string $type
     * @param int $storeId
     * @return string
     */
    public function resolve($price, $type, $storeId)
    {
        if ($type == BaseOffType::FIXED) {
            return $this->priceFormatter->getFormattedFixedPriceByStore($price, $storeId);
        }
        return $this->priceFormatter->getFormattedPercentPrice($price);
    }
}
