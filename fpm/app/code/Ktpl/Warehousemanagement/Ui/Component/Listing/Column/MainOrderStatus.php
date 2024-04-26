<?php

namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

class MainOrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('In Warehouse')],
            ['value' => 1, 'label' => __('Out of Warehouse')]
        ];
    }
}
