<?php

namespace CAT\Bnpl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Environment implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => 'staging', 'label' => __('Staging')],
            ['value' => 'production', 'label' => __('Production')]
        ];
        return $options;
    }
}
