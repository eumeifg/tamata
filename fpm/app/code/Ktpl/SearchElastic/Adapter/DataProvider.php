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

namespace Ktpl\SearchElastic\Adapter;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Ktpl\SearchElastic\Adapter\Aggregation\IntervalFactory;
use Ktpl\SearchElastic\Model\Config;
use Ktpl\SearchElastic\Model\Engine;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Class DataProvider
 *
 * @package Ktpl\SearchElastic\Adapter
 */
class DataProvider implements DataProviderInterface
{
    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Range
     */
    private $range;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $indexerId;

    /**
     * @var IntervalFactory
     */
    private $intervalFactory;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;

    /**
     * DataProvider constructor.
     *
     * @param Range $range
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param Engine $engine
     * @param ResourceConnection $resource
     * @param IntervalFactory $intervalFactory
     * @param IndexScopeResolver $resolver
     * @param string $indexerId
     */
    public function __construct(
        Range $range,
        StoreManagerInterface $storeManager,
        Config $config,
        Engine $engine,
        ResourceConnection $resource,
        IntervalFactory $intervalFactory,
        IndexScopeResolver $resolver,
        $indexerId = 'catalogsearch_fulltext'
    )
    {
        $this->range = $range;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->engine = $engine;
        $this->resource = $resource;
        $this->indexerId = $indexerId;
        $this->resolver = $resolver;
        $this->intervalFactory = $intervalFactory;
    }

    /**
     * Get range
     *
     * @return array|int
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * Get aggregations
     *
     * @param EntityStorage $entityStorage
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Exception
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];

        $entityIds = $entityStorage->getSource();

        if ($entityIds instanceof \Magento\Framework\DB\Ddl\Table) {
            $connection = $this->resource->getConnection();

            $select = $connection->select()->from($entityIds->getName(), ['entity_id']);

            $entityIds = $connection->fetchAll($select);
        }

        $dimension = new Dimension('scope', $this->storeManager->getStore()->getId());

        $indexName = $this->resolver->resolve($this->indexerId, [$dimension]);

        $requestQuery = [
            'index' => $this->config->getIndexName($indexName),
            'type' => Config::DOCUMENT_TYPE,
            '_source' => false,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [
                                    '_id' => $entityIds,
                                ],
                            ],
                        ],
                    ],
                ],
                'aggregations' => [
                    'price_raw' => [
                        'extended_stats' => [
                            'field' => 'price_raw',
                        ],
                    ],
                ],
            ],
        ];

        $queryResult = $this->engine->getClient()
            ->search($requestQuery);

        if (isset($queryResult['aggregations']['price_raw'])) {
            $aggregations = [
                'count' => $queryResult['aggregations']['price_raw']['count'],
                'max' => $queryResult['aggregations']['price_raw']['max'],
                'min' => $queryResult['aggregations']['price_raw']['min'],
                'std' => $queryResult['aggregations']['price_raw']['std_deviation'],
            ];
        }

        return $aggregations;
    }

    /**
     * Get interval
     *
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param EntityStorage $entityStorage
     * @return \Magento\Framework\Search\Dynamic\IntervalInterface
     */
    public function getInterval(BucketInterface $bucket, array $dimensions, EntityStorage $entityStorage)
    {
        $entityIds = $entityStorage->getSource();
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();

        return $this->intervalFactory->create([
            'entityIds' => $entityIds,
            'storeId' => $storeId,
            'fieldName' => 'price',
        ]);
    }

    /**
     * Get aggregation
     *
     * @param BucketInterface $bucket
     * @param array $dimensions
     * @param int $range
     * @param EntityStorage $entityStorage
     * @return array
     * @throws \Zend_Db_Exception
     */
    public function getAggregation(BucketInterface $bucket, array $dimensions, $range, EntityStorage $entityStorage)
    {
        $result = [];
        $entityIds = $entityStorage->getSource();

        if (is_object($entityIds) && $entityIds instanceof \Magento\Framework\DB\Ddl\Table) {
            $select = $this->resource->getConnection()->select()
                ->from($entityIds->getName(), ['entity_id']);
            $entityIds = $this->resource->getConnection()->fetchCol($select);
        }

        $fieldName = $bucket->getField();

        $indexName = $this->resolver->resolve($this->indexerId, $dimensions);

        $requestQuery = [
            'index' => $this->config->getIndexName($indexName),
            'type' => Config::DOCUMENT_TYPE,
            'body' => [
                'stored_fields' => [
                    '_id',
                    '_score',
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [
                                    '_id' => $entityIds,
                                ],
                            ],
                        ],
                    ],
                ],
                'aggregations' => [
                    'price_raw' => [
                        'histogram' => [
                            'field' => $fieldName . '_raw',
                            'interval' => $range,
                        ],
                    ],
                ],
            ],
        ];

        try {
            $queryResult = $this->engine->getClient()
                ->search($requestQuery);
            foreach ($queryResult['aggregations']['price_raw']['buckets'] as $bucket) {
                $key = intval($bucket['key'] / $range + 1);
                $result[$key] = $bucket['doc_count'];
            }
        } catch (\Exception $e) {
        }

        return $result;
    }

    /**
     * Prepare data
     *
     * @param int $range
     * @param array $dbRanges
     * @return array
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];
            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;
                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }
}
