<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Commissions
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Commissions\Model\Source;

class CommissionType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray($allowGlobal = false)
    {
        $options =  [
            ['label' => __('Product'), 'value' => 1],
            ['label' => __('Category'), 'value' => 2],
            ['label' => __('Vendor'), 'value' => 3],
        ];
        if ($allowGlobal) {
            array_unshift($options, ['label' => __('Global'), 'value' => 0]);
        }
        return $options;
    }
    
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Global'), 1 => __('Product'), 2 => __('Category'), 3 => __('Vendor')];
    }
}
