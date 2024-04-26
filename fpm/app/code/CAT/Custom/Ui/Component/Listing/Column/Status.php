<?php


namespace CAT\Custom\Ui\Component\Listing\Column;


class Status implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Pending')],
            ['value' => 2, 'label' => __('Ready for Indexing')],
            ['value' => 3, 'label' => __('Indexing Started')],
            ['value' => 1, 'label' => __('Completed')]
        ];
    }
}
