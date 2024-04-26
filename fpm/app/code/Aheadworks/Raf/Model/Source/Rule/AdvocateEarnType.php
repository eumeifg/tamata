<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Rule;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AdvocateEarnType
 * @package Aheadworks\Raf\Model\Source\Rule
 */
class AdvocateEarnType implements OptionSourceInterface
{
    /**
     * Advocate earn type
     */
    const CUMULATIVE = 'cumulative';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CUMULATIVE, 'label' => __('Cumulative')],
        ];
    }
}
