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

use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\SearchElastic\Model\Config;

/**
 * Class Mapper
 *
 * @package Ktpl\SearchElastic\Adapter
 */
class Mapper
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var IndexBuilderInterface[]
     */
    private $indexProviders;

    /**
     * @var Query\MatchQuery
     */
    private $matchQuery;

    /**
     * @var Query\FilterQuery
     */
    private $filterQuery;

    /**
     * @var Query\AggregationQuery
     */
    private $aggregationQuery;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $nestedTerms = [];

    /**
     * Mapper constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param ConditionManager $conditionManager
     * @param Query\MatchQuery $matchQuery
     * @param Query\FilterQuery $filterQuery
     * @param Query\AggregationQuery $aggregationQuery
     * @param Config $config
     * @param IndexScopeResolver $resolver
     * @param array $indexProviders
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        ConditionManager $conditionManager,
        Query\MatchQuery $matchQuery,
        Query\FilterQuery $filterQuery,
        Query\AggregationQuery $aggregationQuery,
        Config $config,
        IndexScopeResolver $resolver,
        array $indexProviders
    )
    {
        $this->indexRepository = $indexRepository;
        $this->conditionManager = $conditionManager;
        $this->indexProviders = $indexProviders;
        $this->matchQuery = $matchQuery;
        $this->resolver = $resolver;
        $this->config = $config;
        $this->filterQuery = $filterQuery;
        $this->aggregationQuery = $aggregationQuery;
    }

    /**
     * Build mapper query
     *
     * @param RequestInterface $request
     * @return array
     */
    public function buildQuery(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $indexName = $this->resolver->resolve(
                $request->getFrom()['index_name'],
                $request->getDimensions()
            );
        } else {
            $searchIndex = $this->indexRepository->get($request->getIndex());
            $indexName = $this->resolver->resolve(
                $searchIndex->getIdentifier(),
                $request->getDimensions()
            );
        }

        $query = [
            'index' => $this->config->getIndexName($indexName),
            'type' => Config::DOCUMENT_TYPE,
            'body' => [
                'from' => is_scalar($request->getFrom()) ? $request->getFrom() : 0,
                'size' => 1000000 - 1,//$request->getSize(), magento apply sorting AFTER selecting products, so we should return products without limits
                'stored_fields' => ['_id', '_score'],
                'query' => $this->processQuery($request->getQuery(), [], BoolQuery::QUERY_CONDITION_MUST),
                //'_source'       => [],
            ],
        ];

        if (!empty($this->nestedTerms)) {
            $query['body']['query']['bool'][BoolQuery::QUERY_CONDITION_MUST][] = $this->nestedTerms;
        }

        $aggregations = $this->aggregationQuery->build($request);

        if ($aggregations) {
            $query['body']['aggregations'] = $aggregations;
        }

        return $query;
    }

    /**
     * Process query
     *
     * @param RequestQueryInterface $requestQuery
     * @param array $query
     * @param $conditionType
     * @return array
     * @throws \Exception
     */
    protected function processQuery(RequestQueryInterface $requestQuery, array $query, $conditionType)
    {
        switch ($requestQuery->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $requestQuery */
                $query = $this->processBoolQuery($requestQuery, $query);
                break;

            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $requestQuery */
                $query = $this->matchQuery->build($query, $requestQuery);
                break;

            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $requestQuery */
                $query = $this->processFilterQuery($requestQuery, $query);
                break;

            default:
                throw new \Exception("Not implemented");
        }

        return $query;
    }

    /**
     * Process boolean query
     *
     * @param BoolQuery $query
     * @param array $selectQuery
     * @return array
     */
    protected function processBoolQuery(BoolQuery $query, array $selectQuery)
    {
        $selectQuery = $this->processBoolQueryCondition(
            $query->getMust(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_MUST
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getShould(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_SHOULD
        );

        $selectQuery = $this->processBoolQueryCondition(
            $query->getMustNot(),
            $selectQuery,
            BoolQuery::QUERY_CONDITION_NOT
        );

        return $selectQuery;
    }

    /**
     * Process boolean query condition
     *
     * @param array $subQueryList
     * @param array $query
     * @param $conditionType
     * @return array
     * @throws \Exception
     */
    protected function processBoolQueryCondition(array $subQueryList, array $query, $conditionType)
    {
        foreach ($subQueryList as $subQuery) {
            $query = $this->processQuery($subQuery, $query, $conditionType);
        }

        return $query;
    }

    /**
     * Process filter query
     *
     * @param FilterQuery $query
     * @param array $selectQuery
     * @return array
     * @throws \Zend_Json_Exception
     */
    protected function processFilterQuery(FilterQuery $query, array $selectQuery)
    {
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_FILTER:
                $filterQuery = $this->filterQuery->build($query->getReference());
                foreach ($filterQuery['bool'] as $condition => $filter) {
                    $selectQuery['bool'][$condition] = array_merge(
                        isset($selectQuery['bool'][$condition]) ? $selectQuery['bool'][$condition] : [],
                        $filter
                    );

                    if (!in_array($query->getReference()->getField(), ['category_ids', 'visibility']) && !empty($filter)) {

                        if (empty($this->nestedTerms)) {
                            $this->nestedTerms = [
                                'nested' => [
                                    'path' => 'children',
                                    'query' => [
                                        'bool' => [
                                            $condition => [
                                                [
                                                    'term' => [
                                                        'children.is_in_stock_raw' => 1,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ];
                        }

                        $childrenFilter = \Zend_Json::decode(
                            str_replace(
                                $query->getReference()->getField() . '_raw',
                                'children.' . $query->getReference()->getField() . '_raw',
                                json_encode($filter)
                            )
                        );

                        $this->nestedTerms['nested']['query']['bool'][$condition] = array_merge(
                            isset($this->nestedTerms['nested']['query']['bool'][$condition])
                                ? $this->nestedTerms['nested']['query']['bool'][$condition]
                                : [],
                            $childrenFilter);

                        $this->nestedTerms = array_unique($this->nestedTerms);
                    }
                }
                break;
            default:
                throw new \Exception("Filter query not implemented");
        }

        return $selectQuery;
    }
}
