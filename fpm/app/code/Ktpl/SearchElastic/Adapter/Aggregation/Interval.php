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

use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Framework\Search\Request\Dimension;
use Ktpl\SearchElastic\Model\Config;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Ktpl\SearchElastic\Model\Engine;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;

/**
 * Class Interval
 *
 * @package Ktpl\SearchElastic\Adapter\Aggregation
 */
class Interval implements IntervalInterface
{
    const DELTA = 0.9;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $entityIds;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;

    /**
     * Interval constructor.
     *
     * @param Config $config
     * @param Engine $engine
     * @param IndexScopeResolver $resolver
     * @param $fieldName
     * @param $storeId
     * @param $entityIds
     */
    public function __construct(
        Config $config,
        Engine $engine,
        IndexScopeResolver $resolver,
        $fieldName,
        $storeId,
        $entityIds
    )
    {
        $this->config = $config;
        $this->engine = $engine;
        $this->resolver = $resolver;
        $this->fieldName = $fieldName;
        $this->storeId = $storeId;
        $this->entityIds = $entityIds;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $limit
     * @param null $offset
     * @param null $lower
     * @param null $upper
     * @return array
     * @throws \Exception
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $from = $to = [];
        if ($lower) {
            $from = ['gte' => $lower - self::DELTA];
        }
        if ($upper) {
            $to = ['lt' => $upper - self::DELTA];
        }

        $dimension = new Dimension('scope', $this->storeId);

        $indexName = $this->config->getIndexName($this->resolver->resolve(Fulltext::INDEXER_ID, [$dimension]));
        $query = [
            'index' => $indexName,
            'type' => Config::DOCUMENT_TYPE,
            'body' => [
                '_source' => true,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    '_id' => $this->entityIds,
                                ],
                            ],
                            [
                                'range' => [
                                    $this->fieldName . '_raw' => array_merge($from, $to),
                                ],
                            ],
                        ],
                    ],
                ],
                'size' => $limit,
            ],
        ];
        if ($offset) {
            $query['body']['from'] = $offset;
        }

        $result = $this->engine->getClient()->search($query);

        $result = $this->arrayValuesToFloat($result['hits']['hits']);

        while (count($result) < $limit) {
            $result[] = $upper;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param $hits
     * @return array
     */
    private function arrayValuesToFloat($hits)
    {
        $returnPrices = [];
        foreach ($hits as $hit) {
            $returnPrices[] = (float)$hit['_source'][$this->fieldName . '_raw'];
        }

        return $returnPrices;
    }

    /**
     * {@inheritdoc}
     *
     * @param float $data
     * @param int $index
     * @param null $lower
     * @return array|void
     */
    public function loadPrevious($data, $index, $lower = null)
    {
    }

    /**
     * {@inheritdoc}
     *
     * @param float $data
     * @param int $rightIndex
     * @param null $upper
     * @return array|void
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
    }
}
