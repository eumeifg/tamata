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
 * Class ScoreFactorRelatively
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Source
 */
class ScoreFactorRelatively implements ArrayInterface
{
    /**
     * Type score
     */
    const RELATIVELY_SCORE = 'score';

    /**
     * Type popularity
     */
    const RELATIVELY_POPULARITY = 'popularity';

    /**
     * Type rating
     */
    const RELATIVELY_RATING = 'rating';

    /**
     * Get score factor relatively options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::RELATIVELY_SCORE,
                'label' => __('initial score'),
            ],
            [
                'value' => self::RELATIVELY_POPULARITY,
                'label' => __('product popularity (orders)'),
            ],
            [
                'value' => self::RELATIVELY_RATING,
                'label' => __('product rating (reviews)'),
            ],
        ];
    }
}
