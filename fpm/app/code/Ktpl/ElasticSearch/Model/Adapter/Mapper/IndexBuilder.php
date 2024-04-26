<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Model\Adapter\Mapper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Class IndexBuilder for native mysql engine
 *
 * @package Ktpl\ElasticSearch\Model\Adapter\Mapper
 */
class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        if (is_array($request->getFrom())) {
            $indexName = $request->getFrom()['index_name'];
        } else {
            $indexName = $request->getIndex();
        }

        $searchIndexTable = $this->scopeResolver->resolve(
            $indexName,
            $request->getDimensions()
        );

        $select = $this->getSelect()
            ->from(
                ['search_index' => $searchIndexTable],
                ['entity_id' => 'entity_id']
            )->joinLeft(
                ['cea' => new \Zend_Db_Expr('(SELECT 1 as search_weight)')],
                '1=1',
                ''
            );

        return $select;
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getReadConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * Get select
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->getReadConnection()->select();
    }
}
