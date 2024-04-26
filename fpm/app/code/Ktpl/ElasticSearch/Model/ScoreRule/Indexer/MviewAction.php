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

namespace Ktpl\ElasticSearch\Model\ScoreRule\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class MviewAction
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Indexer
 */
class MviewAction implements MviewActionInterface
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * MviewAction constructor.
     *
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param int[] $ids
     */
    public function execute($ids)
    {
        $indexer = $this->indexerRegistry->get(ScoreRuleIndexer::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
