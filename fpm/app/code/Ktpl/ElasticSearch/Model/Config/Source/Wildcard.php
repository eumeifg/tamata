<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class Wildcard
 *
 * @package Ktpl\ElasticSearch\Model\Config\Source
 */
class Wildcard implements ArrayInterface
{
    /**
     * Get wildcard options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::WILDCARD_INFIX,
                'label' => __('Enabled (*word*)'),
            ],
            [
                'value' => Config::WILDCARD_SUFFIX,
                'label' => __('Enabled at end (word*)'),
            ],
            [
                'value' => Config::WILDCARD_PREFIX,
                'label' => __('Enabled at start (*word)'),
            ],
            [
                'value' => Config::WILDCARD_DISABLED,
                'label' => __('Disabled'),
            ],
        ];
    }
}
