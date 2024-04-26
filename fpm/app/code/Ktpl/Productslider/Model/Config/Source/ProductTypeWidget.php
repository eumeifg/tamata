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

/**
 * Class ProductTypeWidget
 * @package Ktpl\Productslider\Model\Config\Source
 */
class ProductTypeWidget extends ProductType
{
    /**
     * @return array
     */
    public function toArray()
    {
        $options = parent::toArray();

        unset($options[self::CATEGORY]);
        unset($options[self::CUSTOM_PRODUCTS]);

        return $options;
    }
}
