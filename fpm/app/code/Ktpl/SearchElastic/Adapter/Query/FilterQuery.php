<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchElastic
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchElastic\Adapter\Query;

use Magento\Framework\Search\Request\FilterInterface;

/**
 * Class FilterQuery
 *
 * @package Ktpl\SearchElastic\Adapter\Query
 */
class FilterQuery
{
    /**
     * FilterQuery constructor.
     *
     * @param Filter\TermFilter $termFilter
     * @param Filter\RangeFilter $rangeFilter
     * @param Filter\WildcardFilter $wildcardFilter
     */
    public function __construct(
        Filter\TermFilter $termFilter,
        Filter\RangeFilter $rangeFilter,
        Filter\WildcardFilter $wildcardFilter
    )
    {
        $this->termFilter = $termFilter;
        $this->rangeFilter = $rangeFilter;
        $this->wildcardFilter = $wildcardFilter;
    }

    /**
     * Build filter query
     *
     * @param FilterInterface $filter
     * @return array
     * @throws \Exception
     */
    public function build(FilterInterface $filter)
    {
        if ($filter->getType() == FilterInterface::TYPE_TERM) {
            /** @var \Magento\Framework\Search\Request\Filter\Term $filter */
            $query = [
                'bool' => [
                    'must' => $this->termFilter->build($filter),
                ],
            ];
        } elseif ($filter->getType() == FilterInterface::TYPE_RANGE) {
            /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
            $query = [
                'bool' => [
                    'must' => $this->rangeFilter->build($filter),
                ],
            ];
        } elseif ($filter->getType() == FilterInterface::TYPE_WILDCARD) {
            /** @var \Magento\Framework\Search\Request\Filter\Wildcard $filter */
            $query = [
                'bool' => [
                    'must' => $this->wildcardFilter->build($filter),
                ],
            ];
        }

        return $query;
    }
}
