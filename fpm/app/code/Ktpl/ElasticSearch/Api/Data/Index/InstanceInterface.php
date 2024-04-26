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

namespace Ktpl\ElasticSearch\Api\Data\Index;

/**
 * Interface InstanceInterface
 *
 * @package Ktpl\ElasticSearch\Api\Data\Index
 */
interface InstanceInterface
{
    /**
     * Index prefix
     */
    const INDEX_PREFIX = 'ktpl_search_';

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Get index name
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Get primary key
     *
     * @return string
     */
    public function getPrimaryKey();

    /**
     * Get data mappers
     *
     * @param string $engine
     * @return DataMapperInterface[]
     */
    public function getDataMappers($engine);

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get attribute weights
     *
     * @return array
     */
    public function getAttributeWeights();

    /**
     * Reindex all
     *
     * @return $this
     */
    public function reindexAll();

    /**
     * Searchable entities (for reindex)
     *
     * @param int $storeId
     * @param null|array $entityIds
     * @param int $lastEntityId
     * @param int $limit
     * @return \Magento\Framework\Data\Collection\AbstractDb|array
     */
    public function getSearchableEntities($storeId, $entityIds = null, $lastEntityId = 0, $limit = 100);

    /**
     * Build Search Collection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function buildSearchCollection();
}
