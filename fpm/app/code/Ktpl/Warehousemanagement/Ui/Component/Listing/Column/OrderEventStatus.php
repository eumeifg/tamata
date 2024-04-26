<?php

namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

class OrderEventStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 2, 'label' => __('Customer Delivery')],
            ['value' => 3, 'label' => __('As part of Product Return')]
        ];
    }
}
