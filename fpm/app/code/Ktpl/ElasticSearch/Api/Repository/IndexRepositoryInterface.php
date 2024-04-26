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

use Ktpl\ElasticSearch\Api\Data\Index\InstanceInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;

/**
 * Interface IndexRepositoryInterface
 *
 * @package Ktpl\ElasticSearch\Api\Repository
 */
interface IndexRepositoryInterface
{
    /**
     * Get collection
     *
     * @return \Ktpl\ElasticSearch\Model\ResourceModel\Index\Collection | IndexInterface[]
     */
    public function getCollection();

    /**
     * Create
     *
     * @return IndexInterface
     */
    public function create();

    /**
     * Get
     *
     * @param int|string $id Index ID or Identifier
     * @return IndexInterface
     */
    public function get($id);

    /**
     * Save
     *
     * @param IndexInterface $index
     * @return IndexInterface
     */
    public function save(IndexInterface $index);

    /**
     * Delete
     *
     * @param IndexInterface $index
     * @return $this
     */
    public function delete(IndexInterface $index);

    /**
     * Get instance
     *
     * @param IndexInterface|string $index
     * @return InstanceInterface
     */
    public function getInstance($index);

    /**
     * Get list
     *
     * @return InstanceInterface[]
     */
    public function getList();
}
