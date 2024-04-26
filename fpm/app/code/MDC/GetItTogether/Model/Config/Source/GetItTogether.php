<?php
namespace MDC\GetItTogether\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Data\OptionSourceInterface;

class GetItTogether implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        return [
            0 => __('No B'),
            1 => __('Yes A'),
        ];
    }
}