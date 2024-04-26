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

namespace Ktpl\ElasticSearch\Model\ScoreRule\PostCondition;

use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Class RequestCondition
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\PostCondition
 */
class RequestCondition extends AbstractCondition
{
    /**
     * Get attribute option
     *
     * @param null $attribute
     * @return array|mixed
     */
    public function getAttributeOption($attribute = null)
    {
        $attributes = [
            'query' => 'Search Query',
        ];

        return $attribute ? $attributes[$attribute] : $attributes;
    }
}
