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

use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Class AggregationQuery
 *
 * @package Ktpl\SearchElastic\Adapter\Query
 */
class AggregationQuery
{
    /**
     * Build aggregation query
     *
     * @param RequestInterface $request
     * @return array
     */
    public function build(
        RequestInterface $request
    )
    {
        $query = [];

        $buckets = $request->getAggregation();
        foreach ($buckets as $bucket) {
            $query = array_merge_recursive($query, $this->buildBucket($bucket));
        }

        return $query;
    }

    /**
     * Build bucket
     *
     * @param BucketInterface $bucket
     * @return array
     */
    protected function buildBucket(
        BucketInterface $bucket
    )
    {
        $field = $bucket->getField();

        if ($bucket->getType() == BucketInterface::TYPE_TERM) {
            $result = [
                $bucket->getName() => [
                    'terms' => [
                        'field' => $field . '_raw',
                        'size' => 500,
                    ],
                ],
            ];

            if (!in_array($field, ['category_ids', 'stock_status'])) {

                $result = [
                    $bucket->getName() => [
                        'nested' => [
                            'path' => 'children',
                        ],
                        'aggregations' => [
                            'child-filter' => [
                                'filter' => [
                                    'term' => [
                                        'children.is_in_stock_raw' => 1,
                                    ],
                                ],
                                'aggregations' => [
                                    $bucket->getName() => [
                                        'terms' => [
                                            'field' => 'children.' . $field . '_raw',
                                            'size' => 500,
                                        ],
                                        'aggregations' => [
                                            $bucket->getName() . '_count' => [
                                                'reverse_nested' => (object)[],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }

            return $result;
        } elseif ($bucket->getType() == BucketInterface::TYPE_DYNAMIC) {
            return [
                $bucket->getName() => [
                    'extended_stats' => [
                        'field' => $field . '_raw',
                    ],
                ],
            ];
        }

        return [];
    }
}
