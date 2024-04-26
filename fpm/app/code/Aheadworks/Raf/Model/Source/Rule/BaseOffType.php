<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Rule;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class BaseOffType
 *
 * @package Aheadworks\Raf\Model\Source\Rule
 */
class BaseOffType implements OptionSourceInterface
{
    /**
     * Fixed off type
     */
    const FIXED = 'fixed';

    /**
     * Percent off type
     */
    const PERCENT = 'percent';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIXED, 'label' => __('Fixed')],
            ['value' => self::PERCENT, 'label' => __('Percent')]
        ];
    }
}
