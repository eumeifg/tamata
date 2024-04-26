<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Source\Transaction;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class EntityType
 *
 * @package Aheadworks\Raf\Model\Source
 */
class EntityType implements ArrayInterface
{
    /**#@+
     * Entity type values
     */
    const ORDER_ID = 'order_id';
    const CREDIT_MEMO_ID = 'credit_memo_id';
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ORDER_ID,
                'label' => __('Order Id')
            ],
            [
                'value' => self::CREDIT_MEMO_ID,
                'label' => __('Credit Memo Id')
            ]
        ];
    }
}
