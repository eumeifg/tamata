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

use Magento\Framework\Search\RequestInterface;
use Ktpl\SearchElastic\Adapter\DataProvider;

/**
 * Class Builder
 *
 * @package Ktpl\SearchElastic\Adapter\Aggregation
 */
class Builder
{
    /**
     * @var DynamicBucket
     */
    private $dynamicBucket;

    /**
     * @var TermBucket
     */
    private $termBucket;

    /**
     * Builder constructor.
     *
     * @param DynamicBucket $dynamicBucket
     * @param TermBucket $termBucket
     * @param DataProvider $dataProvider
     */
    public function __construct(
        DynamicBucket $dynamicBucket,
        TermBucket $termBucket,
        DataProvider $dataProvider
    )
    {
        $this->dynamicBucket = $dynamicBucket;
        $this->termBucket = $termBucket;
        $this->dataProvider = $dataProvider;
    }

    /**
     * Extract request interface
     *
     * @param RequestInterface $request
     * @param array $response
     * @return array
     * @throws \Exception
     */
    public function extract(RequestInterface $request, array $response)
    {
        $aggregations = [];
        $buckets = $request->getAggregation();

        foreach ($buckets as $bucket) {
            if ($bucket->getType() == 'dynamicBucket') {
                $aggregations[$bucket->getName()] = $this->dynamicBucket->build(
                    $bucket,
                    $request->getDimensions(),
                    $response,
                    $this->dataProvider
                );
            } elseif ($bucket->getType() == 'termBucket') {
                $aggregations[$bucket->getName()] = $this->termBucket->build(
                    $bucket,
                    $response
                );
            } else {
                throw new \Exception("Bucket type not implemented.");
            }
        }

        return $aggregations;
    }
}
