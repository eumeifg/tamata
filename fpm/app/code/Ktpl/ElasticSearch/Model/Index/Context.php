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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class Context
 *
 * @package Ktpl\ElasticSearch\Model\Index
 */
class Context
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Searcher
     */
    private $searcher;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Context constructor.
     *
     * @param IndexerFactory $indexerFactory
     * @param SearcherFactory $searcherFactory
     * @param ResourceConnection $resourceConnection
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param ContextFactory $contextFactory
     * @param IndexRepositoryInterface $indexRepository
     * @param RequestInterface $request
     */
    public function __construct(
        IndexerFactory $indexerFactory,
        SearcherFactory $searcherFactory,
        ResourceConnection $resourceConnection,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Config $config,
        ContextFactory $contextFactory,
        IndexRepositoryInterface $indexRepository,
        RequestInterface $request
    )
    {
        $this->indexer = $indexerFactory->create();
        $this->searcher = $searcherFactory->create();
        $this->resourceConnection = $resourceConnection;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->contextFactory = $contextFactory;
        $this->indexRepository = $indexRepository;
        $this->request = $request;
    }

    /**
     * Get content instance
     *
     * @return Context
     */
    public function getInstance()
    {
        return $this->contextFactory->create();
    }

    /**
     * Get indexer
     *
     * @return Indexer
     */
    public function getIndexer()
    {
        return $this->indexer;
    }

    /**
     * Get searcher
     *
     * @return Searcher
     */
    public function getSearcher()
    {
        return $this->searcher;
    }

    /**
     * Get resource connection
     *
     * @return ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }

    /**
     * Get object manager instance
     *
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Get store manager
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager()
    {
        return $this->storeManager;
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get index repository
     *
     * @return IndexRepositoryInterface
     */
    public function getIndexRepository()
    {
        return $this->indexRepository;
    }

    /**
     * Get request instance
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
