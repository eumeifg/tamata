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

namespace Ktpl\SearchElastic\Adapter\Query\Filter;

use Magento\Framework\Search\Request\Filter\Range;

/**
 * Class RangeFilter
 *
 * @package Ktpl\SearchElastic\Adapter\Query\Filter
 */
class RangeFilter
{
    /**
     * Build range filter
     *
     * @param Range $filter
     * @return array
     */
    public function build(Range $filter)
    {
        $query = [];

        if ($filter->getFrom()) {
            $query['range'][$filter->getField() . '_raw']['gte'] = $filter->getFrom();
        }

        if ($filter->getTo()) {
            $query['range'][$filter->getField() . '_raw']['lte'] = $filter->getTo();
        }

        return [$query];
    }
}
