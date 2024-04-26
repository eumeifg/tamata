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

class CalculationType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const FLAT_LABEL = 'Flat';
    const FLAT_VALUE = 1;
    const PERCENTAGE_LABEL = 'Percentage';
    const PERCENTAGE_VALUE = 2;
    const CALCULATION_TYPES = [self::FLAT_VALUE =>self::FLAT_LABEL, self::PERCENTAGE_VALUE => self::PERCENTAGE_LABEL];
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __(self::FLAT_LABEL), 'value' => self::FLAT_VALUE],
            ['label' => __(self::PERCENTAGE_LABEL), 'value' => self::PERCENTAGE_VALUE]
        ];
    }
    
    /**
     *
     * @return type
     */
    public function getAllOptions()
    {
        return [
            ['label' => __(self::FLAT_LABEL), 'value' => self::FLAT_VALUE],
            ['label' => __(self::PERCENTAGE_LABEL), 'value' => self::PERCENTAGE_VALUE]
        ];
    }
    
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return self::CALCULATION_TYPES;
    }
}
