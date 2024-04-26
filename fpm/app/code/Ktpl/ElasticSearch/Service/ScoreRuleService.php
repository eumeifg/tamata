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

namespace Ktpl\ElasticSearch\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Ktpl\ElasticSearch\Api\Repository\ScoreRuleRepositoryInterface;
use Ktpl\ElasticSearch\Model\ScoreRule\Indexer\ScoreRuleIndexer;

/**
 * Class ScoreRuleService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class ScoreRuleService
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScoreRuleRepositoryInterface
     */
    private $scoreRuleRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ScoreRuleService constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param ScoreRuleRepositoryInterface $scoreRuleRepository
     * @param RequestInterface $request
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        ScoreRuleRepositoryInterface $scoreRuleRepository,
        RequestInterface $request
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->scoreRuleRepository = $scoreRuleRepository;
        $this->request = $request;
    }

    /**
     * Apply scores
     *
     * @param Table $table
     * @return Table
     * @throws \Zend_Db_Exception
     */
    public function applyScores(Table $table)
    {
        $connection = $this->resource->getConnection();

        if (!$connection->isTableExists($this->getIndexTable())) {
            return $table;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $storeIds = [0, $storeId];

        $ruleIds = [0];// include Search Weight Virtual Rule

        foreach ($this->getApplicableScoreRules() as $scoreRule) {
            $ruleIds[] = $scoreRule->getId();
        }

        $select = $connection->select()->from(['index' => $this->getIndexTable()], ['*'])
            ->joinLeft(['data' => $table->getName()], 'index.product_id = data.entity_id', [])
            ->where('data.entity_id > ?', 0)
            ->where('index.store_id IN (?)', $storeIds)
            ->where('index.rule_id IN (?)', $ruleIds);

        $rows = $connection->fetchAll($select);

        $actions = [];
        foreach ($rows as $row) {
            $scoreFactor = $row[ScoreRuleIndexer::SCORE_FACTOR];
            if ($scoreFactor === '+0') {
                continue;
            }

            $actions[$scoreFactor][] = $row[ScoreRuleIndexer::PRODUCT_ID];
        }

        foreach ($actions as $action => $productIds) {
            $productIds = array_filter($productIds);

            $this->leadTo100($table);

            $connection->update(
                $table->getName(),
                ['score' => new \Zend_Db_Expr("score" . $action)],
                ['entity_id IN (' . implode(',', $productIds) . ')']
            );
        }

        return $table;
    }

    /**
     * Get applicable score rules
     *
     * @return ScoreRuleInterface[]
     */
    private function getApplicableScoreRules()
    {
        $result = [];
        $storeId = $this->storeManager->getStore()->getId();

        $scoreRules = $this->scoreRuleRepository->getCollection()
            ->addFieldToFilter(ScoreRuleInterface::IS_ACTIVE, 1);

        /** @var ScoreRuleInterface $scoreRule */
        foreach ($scoreRules as $scoreRule) {
            if (!in_array($storeId, $scoreRule->getStoreIds())) {
                continue;
            }

            if ($scoreRule->getActiveFrom() && strtotime($scoreRule->getActiveFrom()) > time()) {
                continue;
            }

            if ($scoreRule->getActiveTo() && strtotime($scoreRule->getActiveTo()) < time()) {
                continue;
            }

            $rule = $scoreRule->getRule();
            $obj = new \Ktpl\ElasticSearch\Model\ScoreRule\DataObject();
            $obj->setData([
                'query' => $this->request->getParam('q'),
            ]);

            if (!$rule->getPostConditions()->validate($obj)) {
                continue;
            }

            $result[] = $scoreRule;
        }

        return $result;
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function leadTo100(Table $table)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($table->getName(), [new \Zend_Db_Expr('MAX(score)')]);

        $maxScore = $connection->fetchOne($select);

        $connection->update($table->getName(), ['score' => new \Zend_Db_Expr("score / $maxScore * 100")]);

        return $table;
    }

    /**
     * Get index table name
     *
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }
}
