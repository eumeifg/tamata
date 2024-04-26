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
 * Class ScoreFactorUnit
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Source
 */
class ScoreFactorUnit implements ArrayInterface
{
    /**
     * Score factor unit options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '*', 'label' => __('times')],
            ['value' => '+', 'label' => __('points')],
        ];
    }
}
