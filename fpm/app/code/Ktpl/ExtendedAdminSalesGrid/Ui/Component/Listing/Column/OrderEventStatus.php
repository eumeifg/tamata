<?php

namespace Ktpl\ExtendedAdminSalesGrid\Ui\Component\Listing\Column;

class OrderEventStatus implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => "pending", 'label' => __('New')],
            ['value' => "canceled", 'label' => __('Canceled')],
            ['value' => "complete", 'label' => __('Complete')],
            ['value' => "confirmed", 'label' => __('Confirmed')],
            ['value' => "processing", 'label' => __('Processing')],
            ['value' => "packed", 'label' => __('Packed')],
            ['value' => "shipped", 'label' => __('Handover')],
            ['value' => "in_transit", 'label' => __('In Transit')]
        ];

    }
}
