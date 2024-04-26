<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Effect
 *
 * @package Ktpl\BannerManagement\Model\Config\Source
 */
class Effect implements ArrayInterface
{
    const SLIDER           = 'slider';
    const FADE_OUT         = 'fadeOut';
    const ROTATE_OUT       = 'rotateOut';
    const FLIP_OUT         = 'flipOutX';
    const ROLL_OUT         = 'rollOut';
    const ZOOM_OUT         = 'zoomOut';
    const SLIDER_OUT_LEFT  = 'slideOutLeft';
    const SLIDER_OUT_RIGHT = 'slideOutRight';
    const LIGHT_SPEED_OUT  = 'lightSpeedOut';

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::SLIDER,
                'label' => __('No')
            ],
            [
                'value' => self::FADE_OUT,
                'label' => __('fadeOut')
            ],
            [
                'value' => self::ROTATE_OUT,
                'label' => __('rotateOut')
            ],
            [
                'value' => self::FLIP_OUT,
                'label' => __('flipOut')
            ],
            [
                'value' => self::ROLL_OUT,
                'label' => __('rollOut')
            ],
            [
                'value' => self::ZOOM_OUT,
                'label' => __('zoomOut')
            ],
            [
                'value' => self::SLIDER_OUT_LEFT,
                'label' => __('slideOutLeft')
            ],
            [
                'value' => self::SLIDER_OUT_RIGHT,
                'label' => __('slideOutRight')
            ],
            [
                'value' => self::LIGHT_SPEED_OUT,
                'label' => __('lightSpeedOut')
            ],
        ];

        return $options;
    }
}
