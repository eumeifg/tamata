<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace MDC\MobileBanner\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 *
 * @package Ktpl\BannerManagement\Model\Config\Source
 */
class PageType implements ArrayInterface
{
    const PRODUCT   = 'product';
    const CATEGORY = 'category';
    const CMS = 'cms';
    const MICROSITE = 'microsite';

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::PRODUCT,
                'label' => __('Product')
            ],
            [
                'value' => self::CATEGORY,
                'label' => __('Category')
            ],
            [
                'value' => self::CMS,
                'label' => __('CMS')
            ],
            [
                'value' => self::MICROSITE,
                'label' => __('Microsite')
            ]
        ];

        return $options;
    }
}
