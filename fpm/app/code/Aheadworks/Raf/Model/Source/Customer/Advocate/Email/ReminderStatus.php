<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Customer\Advocate\Email;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ReminderStatus
 *
 * @package Aheadworks\Raf\Model\Source\Customer\Advocate\Email
 */
class ReminderStatus implements OptionSourceInterface
{
    /**#@+
     * Transaction action values
     */
    const READY_TO_BE_SENT = 'ready_to_be_sent';
    const SENT = 'sent';
    const FAILED = 'failed';
    /**#@-*/

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::READY_TO_BE_SENT,
                'label' => __('Ready to be Sent')
            ],
            [
                'value' => self::SENT,
                'label' => __('Sent')
            ],
            [
                'value' => self::FAILED,
                'label' => __('Failed')
            ]
        ];
    }
}
