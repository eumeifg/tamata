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

use Ktpl\SearchElastic\Model\Config;
use Ktpl\SearchElastic\Model\Engine;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Ktpl\SearchElastic\Adapter\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\Adapter\Mysql\Adapter as MysqlAdapter;

/**
 * Class ElasticAdapter
 *
 * @package Ktpl\SearchElastic\Adapter
 */
class ElasticAdapter implements AdapterInterface
{
    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var MysqlAdapter
     */
    private $mysqlAdapter;

    /**
     * ElasticAdapter constructor.
     *
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param Engine $engine
     * @param Config $config
     * @param MysqlAdapter $mysqlAdapter
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        Engine $engine,
        Config $config,
        MysqlAdapter $mysqlAdapter
    )
    {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->engine = $engine;
        $this->config = $config;
        $this->mysqlAdapter = $mysqlAdapter;
    }

    /**
     * Build query
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Search\Response\QueryResponse
     * @throws \Exception
     */
    public function query(RequestInterface $request)
    {
        $client = $this->engine->getClient();
        $query = $this->mapper->buildQuery($request);

        if (!$this->engine->isAvailable()) {
            return $this->mysqlAdapter->query($request);
        }

        if ($request->getName() == 'quick_search_container'
            || $request->getName() == 'catalog_view_container'
            || $request->getName() == 'catalogsearch_fulltext'
        ) {
            $query = $this->filterByStockStatus($query);
        }

        if (filter_input(INPUT_GET, 'debug') !== null) {
            var_dump($query);
        }

        $attempt = 0;
        $response = false;
        $exception = false;

        while ($attempt < 5 && $response === false) {
            $attempt++;

            try {
                $response = $client->search($query);
            } catch (\Exception $e) {
                $exception = $e;
            }
        }

        if (!$response && $exception) {
            throw $exception;
        }

        if (filter_input(INPUT_GET, 'debug') !== null) {
            var_dump($response);
        }

        $hits = isset($response['hits']['hits']) ? $response['hits']['hits'] : [];
        $hits = array_slice($hits, 0, $this->config->getResultsLimit());

        $documents = [];
        foreach ($hits as $doc) {
            $d = [
                'id' => $doc['_id'],
                'entity_id' => $doc['_id'],
                'score' => $doc['_score'],
                'data' => isset($doc['_source']) ? $doc['_source'] : [],
            ];

            $documents[] = $d;
        }

        return $this->responseFactory->create([
            'documents' => $documents,
            'aggregations' => $this->aggregationBuilder->extract($request, $response),
        ]);
    }

    /**
     * Filter query by stock status
     *
     * @param array $query
     * @return array
     */
    private function filterByStockStatus($query)
    {
        if ($this->config->isShowOutOfStock() == false) {
            $query['body']['query']['bool']['must'][] = [
                'term' => [
                    'is_in_stock_raw' => 1,
                ],
            ];
        }

        return $query;
    }
}
