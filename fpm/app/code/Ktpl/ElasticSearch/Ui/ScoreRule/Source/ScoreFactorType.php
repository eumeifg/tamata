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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ScoreFactorType
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Source
 */
class ScoreFactorType implements ArrayInterface
{
    /**
     * Get score factor type options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '+', 'label' => __('Increase by')],
            ['value' => '-', 'label' => __('Decrease by')],
        ];
    }
}
