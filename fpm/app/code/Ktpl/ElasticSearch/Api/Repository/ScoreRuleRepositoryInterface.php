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

use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;

/**
 * Interface ScoreRuleRepositoryInterface
 *
 * @package Ktpl\ElasticSearch\Api\Repository
 */
interface ScoreRuleRepositoryInterface
{
    /**
     * Get collection
     *
     * @return \Ktpl\ElasticSearch\Model\ResourceModel\ScoreRule\Collection | ScoreRuleInterface[]
     */
    public function getCollection();

    /**
     * Create
     *
     * @return ScoreRuleInterface
     */
    public function create();

    /**
     * Get by id
     *
     * @param int $id
     * @return ScoreRuleInterface
     */
    public function get($id);

    /**
     * Save
     *
     * @param ScoreRuleInterface $model
     * @return $this
     */
    public function save(ScoreRuleInterface $model);

    /**
     * Delete
     *
     * @param ScoreRuleInterface $model
     * @return $this
     */
    public function delete(ScoreRuleInterface $model);
}
