<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Transaction;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 *
 * @package Aheadworks\Raf\Model\Source\Transaction
 */
class Status implements OptionSourceInterface
{
    /**#@+
     * Transaction status values
     */
    const PENDING = 'pending';
    const COMPLETE = 'complete';
    const CANCELED = 'canceled';
    /**#@-*/

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => self::COMPLETE,
                'label' => __('Complete')
            ],
            [
                'value' => self::CANCELED,
                'label' => __('Canceled')
            ]
        ];
    }
}
