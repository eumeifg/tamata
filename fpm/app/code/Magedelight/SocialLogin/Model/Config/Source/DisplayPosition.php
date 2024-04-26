<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\SocialLogin\Model\Config\Source;

class DisplayPosition implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => 'top', 'label' => __('Top')],
                ['value' => 'bottom', 'label' => __('Bottom')],
                ['value' => 'left', 'label' => __('Left')],
                ['value' => 'right', 'label' => __('Right')],
        ];
    }
}
