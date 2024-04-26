<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchMysql
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchMysql\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder as AggregationBuilder;
use Magento\Framework\Search\Adapter\Mysql\ResponseFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Ktpl\ElasticSearch\Model\Config as SearchConfig;

/**
 * Class Adapter
 *
 * @package Ktpl\SearchMysql\Adapter
 */
class Adapter implements AdapterInterface
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
     * @var SearchConfig
     */
    protected $searchConfig;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;
    /**
     * @var TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * Adapter constructor.
     *
     * @param Mapper $mapper
     * @param ResponseFactory $responseFactory
     * @param ResourceConnection $resource
     * @param AggregationBuilder $aggregationBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param SearchConfig $searchConfig
     */
    public function __construct(
        Mapper $mapper,
        ResponseFactory $responseFactory,
        ResourceConnection $resource,
        AggregationBuilder $aggregationBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        SearchConfig $searchConfig
    )
    {
        $this->mapper = $mapper;
        $this->responseFactory = $responseFactory;
        $this->resource = $resource;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->searchConfig = $searchConfig;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Search\Response\QueryResponse
     * @throws \Zend_Db_Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function query(RequestInterface $request)
    {
        $this->resource->getConnection()->query('SET SESSION group_concat_max_len = 1000000;');

        $query = $this->mapper->buildQuery($request);

        if ($request->getName() == 'quick_search_container') {
            $query->limit($this->searchConfig->getResultsLimit());

            //ability to search by 0 attribute (options)
            $from = $this->processFromPart($query->getPart('FROM'));
            $query->setPart('FROM', $from);
        }

        if (filter_input(INPUT_GET, 'debug') !== null) {
            var_dump("<pre>$query</pre>");
        }

        $temporaryStorage = $this->temporaryStorageFactory->create();

        $table = $temporaryStorage->storeDocumentsFromSelect($query);

        return $this->responseFactory->create([
            'documents' => $this->getDocuments($table),
            'aggregations' => $this->aggregationBuilder->build($request, $table),
        ]);
    }

    /**
     * Process from part
     *
     * @param $from
     * @return mixed
     */
    private function processFromPart($from)
    {
        foreach (array_keys($from) as $k) {
            $fromConditions = explode('INNER JOIN', $from[$k]['tableName']);
            foreach ($fromConditions as $key => $condition) {
                if (strpos($condition, 'search_index.attribute_id = cea.attribute_id') !== false) {
                    $from[$k]['tableName'] = str_replace('INNER JOIN' . $condition, 'LEFT JOIN' . $condition, $from[$k]['tableName']);
                    $from[$k]['tableName'] = new \Zend_Db_Expr('(' . $from[$k]['tableName'] . ')');
                }
            }
        }

        return $from;
    }

    /**
     * Executes query and return raw response
     *
     * @param Table $table
     * @return array
     * @throws \Zend_Db_Exception
     */
    private function getDocuments(Table $table)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->from($table->getName(), ['entity_id', 'score']);

        return $connection->fetchAssoc($select);
    }

    /**
     * Get connection
     *
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
