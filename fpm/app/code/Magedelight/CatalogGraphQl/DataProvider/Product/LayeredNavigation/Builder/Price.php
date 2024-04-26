<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilderInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\BucketInterface;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Formatter\LayerFormatter;

/**
 * @inheritdoc
 */
class Price extends \Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Price
{
    /**
     * @var string
     */
    private const PRICE_BUCKET = 'price_bucket';

    /**
     * @var LayerFormatter
     */
    private $layerFormatter;

    /**
     * @var array
     */
    private static $bucketMap = [
        self::PRICE_BUCKET => [
            'request_name' => 'price',
            'label' => 'Price'
        ],
    ];

    /**
     * @param LayerFormatter $layerFormatter
     */
    public function __construct(
        LayerFormatter $layerFormatter
    ) {
        $this->layerFormatter = $layerFormatter;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $bucket = $aggregation->getBucket(self::PRICE_BUCKET);
        if ($this->isBucketEmpty($bucket)) {
            return [];
        }
        $alabel = self::$bucketMap[self::PRICE_BUCKET]['label'];
        if($storeId === 2){
            $alabel = "السعر";
        }
        $result = $this->layerFormatter->buildLayer($alabel,
            \count($bucket->getValues()),
            self::$bucketMap[self::PRICE_BUCKET]['request_name']
        );

        foreach ($bucket->getValues() as $value) {
            $metrics = $value->getMetrics();
            $result['options'][] = $this->layerFormatter->buildItem(
                \str_replace('_', '-', $metrics['value']),
                $metrics['value'],
                $metrics['count']
            );
        }

        return [$result];
    }

    /**
     * Check that bucket contains data
     *
     * @param BucketInterface|null $bucket
     * @return bool
     */
    private function isBucketEmpty(?BucketInterface $bucket): bool
    {
        return null === $bucket || !$bucket->getValues();
    }
}
