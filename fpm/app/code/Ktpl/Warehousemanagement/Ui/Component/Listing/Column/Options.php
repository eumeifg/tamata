<?php

namespace Ktpl\Warehousemanagement\Ui\Component\Listing\Column;

class Options implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'pending', 'label' => __('pending')],
            ['value' => 'processing', 'label' => __('processing')],
            ['value' => 'complete', 'label' => __('complete')],
            ['value' => 'canceled', 'label' => __('canceled')],
            ['value' => 'closed', 'label' => __('closed')],
            ['value' => 'packed', 'label' => __('packed')],
            ['value' => 'confirmed', 'label' => __('confirmed')],
            ['value' => 'shipped', 'label' => __('shipped')],
            ['value' => 'handover', 'label' => __('handover')],
            ['value' => 'in_transit', 'label' => __('in_transit')],
            ['value' => 'delivered', 'label' => __('delivered')],
            ['value' => 'buyer_canceled', 'label' => __('buyer_canceled')],
            ['value' => 'seller_canceled', 'label' => __('seller_canceled')],
            ['value' => 'marketplace_canceled', 'label' => __('marketplace_canceled')],
        ];
    }
}
