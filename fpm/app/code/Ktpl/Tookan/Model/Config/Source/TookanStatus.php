<?php

namespace Ktpl\Tookan\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TookanStatus implements ArrayInterface
{
    const READY_TO_SHIPPED = 1;
    const IN_WAREHOUSE = 2;
    const OUT_FOR_DELIVERY = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::READY_TO_SHIPPED, 'label' => __('Ready to Shipped')],
            ['value' => self::IN_WAREHOUSE, 'label' => __('In Warehouse')],
            ['value' => self::OUT_FOR_DELIVERY, 'label' => __('Out For Delivery')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::READY_TO_SHIPPED => __('Ready to Shipped'),
            self::IN_WAREHOUSE => __('In Warehouse'),
            self::OUT_FOR_DELIVERY => __('Out For Delivery')
        ];
    }
}
