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

namespace Ktpl\SearchElastic\Adapter\Aggregation;

use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;

/**
 * Class TermBucket
 *
 * @package Ktpl\SearchElastic\Adapter\Aggregation
 */
class TermBucket
{
    /**
     * Build term bucket
     *
     * @param RequestBucketInterface $bucket
     * @param array $response
     * @return array
     */
    public function build(
        RequestBucketInterface $bucket,
        array $response
    )
    {
        $values = [];
        if (isset($response['aggregations'][$bucket->getName()]['buckets'])) {
            foreach ($response['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
                $values[$resultBucket['key']] = [
                    'value' => $resultBucket['key'],
                    'count' => $resultBucket['doc_count'],
                ];
            }
        } else {
            foreach ($response['aggregations'][$bucket->getName()]['child-filter'][$bucket->getName()]['buckets'] as $resultBucket) {
                $values[$resultBucket['key']] = [
                    'value' => $resultBucket['key'],
                    'count' => $resultBucket[$bucket->getName() . '_count']['doc_count'],
                ];
            }
        }

        return $values;
    }
}
