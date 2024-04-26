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

namespace Ktpl\ElasticSearch\Model\Index;

use Magento\Search\Model\QueryFactory;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;
use Magento\Search\Model\SearchEngine;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory;
use Magento\Framework\Search\Adapter\Mysql\TemporaryStorage;
use Magento\Framework\App\ScopeResolverInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class Searcher
 *
 * @package Ktpl\ElasticSearch\Model\Index
 */
class Searcher
{
    /**
     * @var AbstractIndex
     */
    protected $index;

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var RequestBuilderFactory
     */
    protected $requestBuilderFactory;

    /**
     * @var SearchEngine
     */
    protected $searchEngine;

    /**
     * @var TemporaryStorageFactory
     */
    protected $temporaryStorageFactory;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var ScopeResolverInterface
     */
    protected $indexRepository;

    /**
     * Searcher constructor.
     *
     * @param QueryFactory $queryFactory
     * @param RequestBuilderFactory $requestBuilderFactory
     * @param SearchEngine $searchEngine
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param ScopeResolverInterface $scopeResolver
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        QueryFactory $queryFactory,
        RequestBuilderFactory $requestBuilderFactory,
        SearchEngine $searchEngine,
        TemporaryStorageFactory $temporaryStorageFactory,
        ScopeResolverInterface $scopeResolver,
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->queryFactory = $queryFactory;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->searchEngine = $searchEngine;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->scopeResolver = $scopeResolver;
        $this->indexRepository = $indexRepository;
    }

    /**
     * Set search index
     *
     * @param AbstractIndex $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Join matches to collection
     *
     * @param AbstractDb $collection
     * @param string $field
     * @return $this
     */
    public function joinMatches($collection, $field = 'e.entity_id')
    {
        $queryResponse = $this->getQueryResponse();

        $temporaryStorage = $this->temporaryStorageFactory->create();

        if ($field == 'ID') {
            //external connection (need improve detection)
            $ids = [0];
            foreach ($queryResponse->getIterator() as $item) {
                $ids[] = $item->getId();
            }

            $collection->getSelect()->where(new \Zend_Db_Expr("$field IN (" . implode(',', $ids) . ")"));
        } else {
            $table = $temporaryStorage->storeDocuments($queryResponse->getIterator());

            $collection->getSelect()->joinInner(
                ['search_result' => $table->getName()],
                $field . ' = search_result.' . TemporaryStorage::FIELD_ENTITY_ID,
                []
            );
        }

        return $this;
    }

    /**
     * Get query response
     *
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse()
    {
        $requestBuilder = $this->requestBuilderFactory->create();

        $queryText = $this->queryFactory->get()->getQueryText();

        $requestBuilder->bind('search_term', $queryText);

        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());

        $requestBuilder->setRequestName($this->index->getIdentifier());

        $requestBuilder->setFrom([
            'index_name' => $this->index->getIndexName(),
            'index_id' => $this->index->getModel()->getId(),
        ]);

        $queryRequest = $requestBuilder->create();

        return $this->searchEngine->search($queryRequest);
    }

    /**
     * Get matched ids
     *
     * @return array
     */
    public function getMatchedIds()
    {
        $requestBuilder = $this->requestBuilderFactory->create();
        $queryText = $this->queryFactory->get()->getQueryText();

        $requestBuilder->bind('search_term', $queryText);

        $requestBuilder->bindDimension('scope', $this->scopeResolver->getScope());

        $requestBuilder->setRequestName($this->index->getIdentifier());

        $requestBuilder->setFrom([
            'index_name' => $this->index->getIndexName(),
            'index_id' => $this->index->getModel()->getId(),
        ]);

        /** @var \Magento\Framework\Search\Request $queryRequest */
        $queryRequest = $requestBuilder->create();

        $queryResponse = $this->searchEngine->search($queryRequest);
        $ids = [];
        foreach ($queryResponse->getIterator() as $item) {
            $ids[] = $item->getId();
        }

        return $ids;
    }
}
