<?php


namespace CAT\CustomerBalance\Ui\Component\Listing\Column;


class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Pending')],
            ['value' => 1, 'label' => __('Completed')]
        ];
    }
}