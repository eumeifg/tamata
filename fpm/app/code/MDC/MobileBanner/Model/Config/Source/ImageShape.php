<?php

namespace MDC\MobileBanner\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ImageShape implements OptionSourceInterface
{
    const LAYOUT_1 = 'layout_1';
    const LAYOUT_2 = 'layout_2';
    const LAYOUT_3 = 'layout_3';
    const LAYOUT_4 = 'layout_4';
    const LAYOUT_5 = 'layout_5';
    const LAYOUT_6 = 'layout_6';
    const LAYOUT_7 = 'layout_7';

    /**
     * @return array[]
     */
    public function toOptionArray() {
        $options = [
            [
                'value' => self::LAYOUT_1,
                'label' => __('Layout 1')
            ],
            [
                'value' => self::LAYOUT_2,
                'label' => __('Layout 2')
            ],
            [
                'value' => self::LAYOUT_3,
                'label' => __('Layout 3')
            ],
            [
                'value' => self::LAYOUT_4,
                'label' => __('Layout 4')
            ],
            [
                'value' => self::LAYOUT_5,
                'label' => __('Layout 5')
            ],
            [
                'value' => self::LAYOUT_6,
                'label' => __('Layout 6')
            ],
            [
                'value' => self::LAYOUT_7,
                'label' => __('Layout 7')
            ]
        ];
        return $options;
    }
}
