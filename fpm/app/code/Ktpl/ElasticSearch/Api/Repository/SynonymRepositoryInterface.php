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

namespace Ktpl\ElasticSearch\Api\Repository;

use Ktpl\ElasticSearch\Api\Data\SynonymInterface;

/**
 * Interface SynonymRepositoryInterface
 *
 * @package Ktpl\ElasticSearch\Api\Repository
 */
interface SynonymRepositoryInterface
{
    /**
     * Get collection
     *
     * @return \Ktpl\ElasticSearch\Model\ResourceModel\Synonym\Collection | SynonymInterface[]
     */
    public function getCollection();

    /**
     * Create
     *
     * @return SynonymInterface
     */
    public function create();

    /**
     * Get by id
     *
     * @param int $id
     * @return SynonymInterface
     */
    public function get($id);

    /**
     * Save
     *
     * @param SynonymInterface $synonym
     * @return $this
     */
    public function save(SynonymInterface $synonym);

    /**
     * Delete
     *
     * @param SynonymInterface $synonym
     * @return $this
     */
    public function delete(SynonymInterface $synonym);
}
