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

namespace Ktpl\ElasticSearch\Repository;

use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Api\Repository\ScoreRuleRepositoryInterface;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterfaceFactory;
use Ktpl\ElasticSearch\Model\ResourceModel\ScoreRule\CollectionFactory;

/**
 * Class ScoreRuleRepository
 *
 * @package Ktpl\ElasticSearch\Repository
 */
class ScoreRuleRepository implements ScoreRuleRepositoryInterface
{
    /**
     * @var ScoreRuleInterfaceFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ScoreRuleRepository constructor.
     *
     * @param ScoreRuleInterfaceFactory $factory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ScoreRuleInterfaceFactory $factory,
        CollectionFactory $collectionFactory
    )
    {
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get collection
     *
     * @return ScoreRuleInterface[]|\Ktpl\ElasticSearch\Model\ResourceModel\ScoreRule\Collection
     */
    public function getCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Get score rule by id
     *
     * @param int $id
     * @return bool|ScoreRuleInterface|\Ktpl\ElasticSearch\Model\ScoreRule
     */
    public function get($id)
    {
        /** @var \Ktpl\ElasticSearch\Model\ScoreRule $model */
        $model = $this->create();
        $model->load($id);

        if (!$model->getId()) {
            return false;
        }

        return $model;
    }

    /**
     * Create score rule factory instance
     *
     * @return ScoreRuleInterface
     */
    public function create()
    {
        return $this->factory->create();
    }

    /**
     * Delete score rule
     *
     * @param ScoreRuleInterface $model
     * @return $this|ScoreRuleRepositoryInterface
     * @throws \Exception
     */
    public function delete(ScoreRuleInterface $model)
    {
        /** @var \Ktpl\ElasticSearch\Model\ScoreRule $model */
        $model->delete();

        return $this;
    }

    /**
     * Save score rule
     *
     * @param ScoreRuleInterface $model
     * @return $this|ScoreRuleRepositoryInterface
     * @throws \Exception
     */
    public function save(ScoreRuleInterface $model)
    {
        /** @var \Ktpl\ElasticSearch\Model\ScoreRule $model */
        $model->save();

        return $this;
    }
}
