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

use Magento\Framework\Search\Request\Filter\Wildcard;

/**
 * Class WildcardFilter
 *
 * @package Ktpl\SearchElastic\Adapter\Query\Filter
 */
class WildcardFilter
{
    /**
     * Build wildcard filter
     *
     * @param Wildcard $filter
     * @return array
     */
    public function build(Wildcard $filter)
    {
        $query = [];

        if ($filter->getValue()) {
            $query['wildcard'][$filter->getField()] = [
                'value' => '*' . strtolower($filter->getValue()) . '*',
            ];
        }

        return [$query];
    }
}
