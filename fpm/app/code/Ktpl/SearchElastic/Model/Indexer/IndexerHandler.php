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

namespace Ktpl\SearchElastic\Model\Indexer;

use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Registry;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\SearchElastic\Model\Engine;
use Ktpl\SearchElastic\Model\Config;
use Ktpl\ElasticSearch\Service\CompatibilityService;

/**
 * Class IndexerHandler
 *
 * @package Ktpl\SearchElastic\Model\Indexer
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * Active index
     */
    const ACTIVE_INDEX = 'active_index';

    /**
     * @var array
     */
    private $data;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var ScopeProxy / IndexScopeResolver
     */
    private $indexScopeResolver;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * IndexerHandler constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param Batch $batch
     * @param Engine $engine
     * @param Config $config
     * @param Registry $registry
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        Batch $batch,
        Engine $engine,
        Config $config,
        Registry $registry,
        array $data,
        $batchSize = 1000
    )
    {
        $this->indexRepository = $indexRepository;
        $this->batch = $batch;
        $this->engine = $engine;
        $this->config = $config;
        $this->registry = $registry;
        $this->data = $data;
        $this->batchSize = $batchSize;
        if (CompatibilityService::is22() || CompatibilityService::is23()) {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy');
        } else {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create('Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver');
        }
    }

    /**
     * Save index data
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface|void
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $index = $this->indexRepository->get($this->getIndexId());
        $instance = $this->indexRepository->getInstance($index);
        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $docs) {
            foreach ($instance->getDataMappers('elastic') as $mapper) {
                $docs = $mapper->map($docs, $dimensions, $index->getIdentifier());
            }

            $this->engine->saveDocuments($indexName, $docs);
        }
    }

    /**
     * Get index id
     *
     * @return string
     */
    private function getIndexId()
    {
        return isset($this->data['index_id'])
            ? $this->data['index_id']
            : $this->indexRepository->get('catalogsearch_fulltext')->getId();
    }

    /**
     * Get index name
     *
     * @return string
     */
    private function getIndexName()
    {
        return $this->data['indexer_id'];
    }

    /**
     * Delete index
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface|void
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);

        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->engine->deleteDocuments($indexName, $batchDocuments);
        }
    }

    /**
     * Clean index
     *
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @return IndexerInterface|void
     */
    public function cleanIndex($dimensions)
    {
        $index = $this->indexRepository->get($this->getIndexId());
        $indexName = $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);

        $this->registry->register(self::ACTIVE_INDEX, $index->getIdentifier(), true);

        $this->engine->cleanDocuments($indexName);
    }

    /**
     * Check if index is available
     *
     * @param array $dimensions
     * @return bool
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }
}
