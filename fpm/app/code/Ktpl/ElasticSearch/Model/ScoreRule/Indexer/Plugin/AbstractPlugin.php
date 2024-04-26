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

namespace Ktpl\ElasticSearch\Model\ScoreRule\Indexer\Plugin;

use Magento\Framework\Indexer\IndexerRegistry;
use Ktpl\ElasticSearch\Model\ScoreRule\Indexer\ScoreRuleIndexer;

/**
 * Class AbstractPlugin
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Indexer\Plugin
 */
abstract class AbstractPlugin
{
    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * AbstractPlugin constructor.
     *
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    )
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int $productId
     * @return void
     */
    protected function reindexRow($productId)
    {
        $indexer = $this->indexerRegistry->get(ScoreRuleIndexer::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexRow($productId);
        }
    }

    /**
     * Reindex by product if indexer is not scheduled
     *
     * @param int[] $productIds
     * @return void
     */
    protected function reindexList(array $productIds)
    {
        $indexer = $this->indexerRegistry->get(ScoreRuleIndexer::INDEXER_ID);
        if (!$indexer->isScheduled()) {
            $indexer->reindexList($productIds);
        }
    }
}
