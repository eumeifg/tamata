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

namespace Ktpl\ElasticSearch\Model;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\IndexServiceInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Ktpl\ElasticSearch\Service\StemmingService;

/**
 * Class AutocompleteProvider
 *
 * @package Ktpl\ElasticSearch\Model
 */
class AutocompleteProvider
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexServiceInterface
     */
    private $indexService;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    private $resourceHelper;

    /**
     * @var StemmingService
     */
    private $stemmingService;

    /**
     * AutocompleteProvider constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexServiceInterface $indexService
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param QueryFactory $queryFactory
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param StemmingService $stemmingService
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        IndexServiceInterface $indexService,
        QueryCollectionFactory $queryCollectionFactory,
        QueryFactory $queryFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Helper $resourceHelper,
        StemmingService $stemmingService
    )
    {
        $this->indexRepository = $indexRepository;
        $this->indexService = $indexService;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
        $this->resourceHelper = $resourceHelper;
        $this->stemmingService = $stemmingService;
    }

    /**
     * Get index repository instance
     *
     * @param $id
     * @return IndexInterface
     */
    public function get($id)
    {
        return $this->indexRepository->get($id);
    }

    /**
     * Get indices
     *
     * @return array
     */
    public function getIndices()
    {
        $indices = [];
        $collection = $this->indexRepository->getCollection()
            ->addFieldToFilter(IndexInterface::IS_ACTIVE, 1);

        /** @var IndexInterface $index */
        foreach ($collection as $index) {
            $indexDataObject = new DataObject([
                'identifier' => $index->getIdentifier(),
                'title' => $index->getTitle(),
                'properties' => $index->getProperties(),
            ]);

            if (in_array($index->getIdentifier(), Config::DISALLOWED_MULTIPLE)) {
                $indexDataObject['index_id'] = $index->getIndexId();
            }

            $indices[] = $indexDataObject;
        }

        $indices[] = new DataObject([
            'title' => __('Popular suggestions')->__toString(),
            'identifier' => 'magento_search_query',
        ]);

        $indices[] = new DataObject([
            'title' => __('Products in categories')->__toString(),
            'identifier' => 'magento_catalog_categoryproduct',
        ]);

        return $indices;
    }

    /**
     * Get query collection
     *
     * @param IndexInterface $index
     * @return array
     */
    public function getCollection($index)
    {
        switch ($index->getIdentifier()) {
            case 'magento_search_query':
                $query = $this->queryFactory->get();
                $queryText = $this->stemmingService->singularize($query->getQueryText());
                $collection = $this->queryCollectionFactory->create();

                $collection->getSelect()->reset(
                    \Magento\Framework\DB\Select::FROM
                )->distinct(
                    true
                )->from(
                    ['main_table' => $collection->getTable('search_query')]
                )->where(
                    'num_results > 0 AND display_in_terms = 1 AND query_text LIKE ?',
                    $this->resourceHelper->addLikeEscape($queryText, ['position' => 'any'])
                )->order(
                    'popularity ' . \Magento\Framework\DB\Select::SQL_DESC
                );

                $collection->addFieldToFilter('query_text', ['nin' => [$query->getQueryText(), $queryText]])
                    ->addStoreFilter([$this->storeManager->getStore()->getId()])
                    ->setOrder('popularity')
                    ->setPageSize(20)
                    ->distinct(true);
                return $collection;

                break;

            case 'magento_catalog_categoryproduct':
                $index = $this->indexRepository->get('catalogsearch_fulltext');
                break;

            default:
                if ($index->getId()) {
                    $index = $this->indexRepository->get($index->getId());
                } else {
                    $index = $this->indexRepository->get($index->getIdentifier());
                }
                break;
        }

        return $this->indexService->getSearchCollection($index);
    }

    /**
     * Retrieve query response
     *
     * @param $index
     * @return bool|\Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index)
    {
        $identifier = $index->getIdentifier();
        $customInstances = [
            'magento_search_query',
            'magento_catalog_categoryproduct',
        ];
        if (in_array($identifier, $customInstances)) {
            return false;
        }
        $index = $this->indexRepository->get($identifier);

        return $this->indexService->getQueryResponse($index);
    }
}
