<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ktpl\SearchAutocomplete\Model\Config;

/**
 * Class Layout
 *
 * @package Ktpl\SearchAutocomplete\Model\Config\Source
 */
class Layout implements ArrayInterface
{
    /**
     * Get column options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::LAYOUT_1_COLUMN,
                'label' => __('1 Column'),
            ],
            [
                'value' => Config::LAYOUT_2_COLUMNS,
                'label' => __('2 Columns'),
            ],
        ];
    }
}
