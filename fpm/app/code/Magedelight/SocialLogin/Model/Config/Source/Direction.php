<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magedelight\SocialLogin\Model\Config\Source;

class Direction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => 'current', 'label' => __('Current Page')],
                ['value' => 'myaccount', 'label' => __('My Account')],
                ['value' => 'homepage', 'label' => __('Homepage')],
        ];
    }
}
