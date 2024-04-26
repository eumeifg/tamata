<?php
/**
 * Ktpl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the KrishTechnolabs.com license that is
 * available through the world-wide-web at this URL:
 * https://https://www.KrishTechnolabs.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ktpl
 * @package     Ktpl_Productslider
 * @copyright   Copyright (c) Ktpl (https://https://www.KrishTechnolabs.com//)
 * @license     https://https://www.KrishTechnolabs.com/LICENSE.txt
 */

namespace Ktpl\Productslider\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Additional
 * @package Ktpl\AutoRelated\Model\Config\Source
 */
class Additional implements ArrayInterface
{
    const SHOW_PRICE  = '1';
    const SHOW_CART   = '2';
    const SHOW_REVIEW = '3';
    const SHOW_WISHLIST = '4';
    const SHOW_COMPARE = '5';
    const SHOW_SWATCH = '6';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::SHOW_PRICE  => __('Price'),
            self::SHOW_CART   => __('Add to cart button'),
            self::SHOW_REVIEW => __('Review information'),
            self::SHOW_WISHLIST => __('Show wishlist'),
            self::SHOW_COMPARE => __('Show compare'),
            self::SHOW_SWATCH => __('Show swatchs')
        ];
    }
}
