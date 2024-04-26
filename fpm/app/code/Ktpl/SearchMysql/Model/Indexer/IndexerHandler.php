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

namespace Ktpl\SearchMysql\Model\Indexer;

use Magento\CatalogSearch\Model\Indexer\IndexStructure;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Indexer\SaveHandler\IndexerInterface;
use Magento\Framework\Search\Request\Dimension;
use Ktpl\ElasticSearch\Service\CompatibilityService;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class IndexerHandler
 *
 * @package Ktpl\SearchMysql\Model\Indexer
 */
class IndexerHandler implements IndexerInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var ScopeProxy
     */
    private $indexScopeResolver;

    /**
     * IndexerHandler constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexStructure $indexStructure
     * @param ResourceConnection $resource
     * @param Batch $batch
     * @param array $data
     * @param int $batchSize
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        IndexStructure $indexStructure,
        ResourceConnection $resource,
        Batch $batch,
        array $data,
        $batchSize = 100
    )
    {
        $this->indexRepository = $indexRepository;

        $this->indexStructure = $indexStructure;
        $this->resource = $resource;
        $this->batch = $batch;
        $this->data = $data;
        $this->batchSize = $batchSize;

        if (CompatibilityService::is22()) {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy');
        } else {
            $this->indexScopeResolver = CompatibilityService::getObjectManager()
                ->create('Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver');
        }
    }

    /**
     * Save index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface|void
     */
    public function saveIndex($dimensions, \Traversable $documents)
    {
        $index = $this->indexRepository->get($this->getIndexId());

        $instance = $this->indexRepository->getInstance($index);
        foreach ($this->batch->getItems($documents, $this->batchSize) as $docs) {
            foreach ($instance->getDataMappers('mysql2') as $dataMapper) {
                $docs = $dataMapper->map($docs, $dimensions, $this->getIndexName());
            }

            $this->insertDocuments($docs, $dimensions);
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
     * Insert documents
     *
     * @param array $documents
     * @param $dimensions
     */
    private function insertDocuments(array $documents, $dimensions)
    {
        $documents = $this->prepareSearchableFields($documents);

        if (empty($documents)) {
            return;
        }
        $this->resource->getConnection()->insertOnDuplicate(
            $this->getTableName($dimensions),
            $documents,
            ['data_index']
        );
    }

    /**
     * Prepare searchable fields
     *
     * @param array $documents
     * @return array
     */
    private function prepareSearchableFields(array $documents)
    {
        $insertDocuments = [];
        foreach ($documents as $entityId => $document) {
            foreach ($document as $attributeId => $fieldValue) {
                $insertDocuments[$entityId . '_' . $attributeId] = [
                    'entity_id' => $entityId,
                    'attribute_id' => $attributeId,
                    'data_index' => $fieldValue,
                ];
            }
        }

        return $insertDocuments;
    }

    /**
     * Get table name
     *
     * @param Dimension[] $dimensions
     * @return string
     */
    private function getTableName($dimensions)
    {
        return $this->indexScopeResolver->resolve($this->getIndexName(), $dimensions);
    }

    /**
     * Delete index
     *
     * @param Dimension[] $dimensions
     * @param \Traversable $documents
     * @return IndexerInterface|void
     */
    public function deleteIndex($dimensions, \Traversable $documents)
    {
        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $this->resource->getConnection()
                ->delete($this->getTableName($dimensions), ['entity_id in (?)' => $batchDocuments]);
        }
    }

    /**
     * Clean index
     *
     * @param Dimension[] $dimensions
     * @return IndexerInterface|void
     */
    public function cleanIndex($dimensions)
    {
        $this->indexStructure->delete($this->getIndexName(), $dimensions);
        $this->indexStructure->create($this->getIndexName(), [], $dimensions);
    }

    /**
     * Check is indexer available
     *
     * @param array $dimensions
     * @return bool
     */
    public function isAvailable($dimensions = [])
    {
        return true;
    }
}
