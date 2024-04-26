<?php
 /**
  * KrishTechnolabs
  *
  * PHP version 7
  *
  * @category  KrishTechnolabs
  * @package   MDC_VendorCommissions
  * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
  * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
  * @link      https://www.krishtechnolabs.com/
  */
namespace MDC\VendorCommissions\Model\Source;

/**
 * Class CommissionType
 */
class CommissionType extends \Magedelight\Commissions\Model\Source\CommissionType
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
            ['label' => __('Vendor Group'), 'value' => 4],
            ['label' => __('Vendor Category'), 'value' => 5]
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
        return [0 => __('Global'), 1 => __('Product'), 2 => __('Category'), 3 => __('Vendor'),4 => __('Vendor Group'),5 => __('Vendor Category')];
    }
}
