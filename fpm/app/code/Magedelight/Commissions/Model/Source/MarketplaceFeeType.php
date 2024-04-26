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

//use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class CalculationType
 */
class MarketplaceFeeType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['label' => __('Flat'), 'value' => '1'],
            ['label' => __('Percentage'), 'value' => '2']
        ];
    }
    
    /**
     *
     * @return type
     */
    public function getAllOptions()
    {
        return [
            ['label' => __('Flat'), 'value' => '1'],
            ['label' => __('Percentage'), 'value' => '2']
        ];
    }
    
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [1 => __('Flat'), 2 => __('Percentage')];
    }
}
