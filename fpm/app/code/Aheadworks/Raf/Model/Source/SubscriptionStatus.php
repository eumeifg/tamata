<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class SubscriptionStatus
 * @package Aheadworks\Raf\Model\Source
 */
class SubscriptionStatus implements OptionSourceInterface
{
    /**#@+
     * Subscribe status values
     */
    const NOT_SUBSCRIBED = 0;
    const SUBSCRIBED = 1;
    /**#@-*/

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::NOT_SUBSCRIBED,
                'label' => __('Not Subscribed')
            ],
            [
                'value' => self::SUBSCRIBED,
                'label' => __('Subscribed')
            ],
        ];
    }
}
