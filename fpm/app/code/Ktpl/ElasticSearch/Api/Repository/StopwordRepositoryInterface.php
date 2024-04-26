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

use Ktpl\ElasticSearch\Api\Data\StopwordInterface;

/**
 * Interface StopwordRepositoryInterface
 *
 * @package Ktpl\ElasticSearch\Api\Repository
 */
interface StopwordRepositoryInterface
{
    /**
     * Get collection
     *
     * @return \Ktpl\ElasticSearch\Model\ResourceModel\Stopword\Collection | StopwordInterface[]
     */
    public function getCollection();

    /**
     * Create
     *
     * @return StopwordInterface
     */
    public function create();

    /**
     * Get by id
     *
     * @param int $id
     * @return StopwordInterface
     */
    public function get($id);

    /**
     * Save
     *
     * @param StopwordInterface $stopword
     * @return $this
     */
    public function save(StopwordInterface $stopword);

    /**
     * Delete
     *
     * @param StopwordInterface $stopword
     * @return $this
     */
    public function delete(StopwordInterface $stopword);
}
