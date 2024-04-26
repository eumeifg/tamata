<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Mobile_Connector
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\MobileInit\Model\Source\Config;

use Magento\Framework\Option\ArrayInterface;

class FrontModules implements ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'Magedelight_Fproduct',
                'config_path' => 'featuredproduct/rb_featured_product/active',
                'label' => __('Featured Products')
            ],
            [
                'value' => 'Magedelight_Tproduct',
                'config_path' => 'trendingproduct/rb_trending_product/active',
                'label' => __('Trending Products')
            ]

        ];
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function getOptions()
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
        foreach ($_tmpOptions as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    public function getModuleOptions($frontModuleArray)
    {
        $_tmpOptions = $this->toOptionArray();
        $_options = [];
       
        foreach ($_tmpOptions as $option) {
            if (in_array($option['value'], $frontModuleArray)) {
                $_options[$option['value']] = $option['config_path'];
            } else {
                $_options[$option['value']] = 0;
            }
        }

        return $_options;
    }
}
