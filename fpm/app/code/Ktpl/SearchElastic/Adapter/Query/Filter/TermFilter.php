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

use Magento\Framework\Search\Request\Filter\Term;

/**
 * Class TermFilter
 *
 * @package Ktpl\SearchElastic\Adapter\Query\Filter
 */
class TermFilter
{
    /**
     * Build term filter
     *
     * @param Term $filter
     * @return array
     */
    public function build(Term $filter)
    {
        $query = [];
        if ($filter->getValue()) {
            $value = $filter->getValue();

            if (is_string($value)) {
                $value = array_filter(explode(',', $value));

                if (count($value) === 1) {
                    $value = $value[0];
                }
            }

            $condition = is_array($value) ? 'terms' : 'term';

            if (is_array($value)) {
                if (key_exists('in', $value)) {
                    $value = $value['in'];
                }

                $value = array_values($value);
            }

            $field = $filter->getField() . '_raw';

            if ($field == 'entity_id_raw') {
                $field = 'entity_id';
            }

            $query[] = [
                $condition => [
                    $field => $value,
                ],
            ];
        }

        return $query;
    }
}
