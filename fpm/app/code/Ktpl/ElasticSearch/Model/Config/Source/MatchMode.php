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
 * Class MatchMode
 *
 * @package Ktpl\ElasticSearch\Model\Config\Source
 */
class MatchMode implements ArrayInterface
{
    /**
     * Get match options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Config::MATCH_MODE_AND,
                'label' => __('AND (preferable)'),
            ],
            [
                'value' => Config::MATCH_MODE_OR,
                'label' => __('OR'),
            ],
        ];
    }
}
