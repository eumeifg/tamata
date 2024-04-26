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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Api\Repository\ScoreRuleRepositoryInterface;
use Ktpl\ElasticSearch\Ui\ScoreRule\Source\ScoreFactorRelatively;

/**
 * Class ScoreRuleIndexer
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Indexer
 */
class ScoreRuleIndexer implements IndexerActionInterface
{
    const INDEXER_ID = 'ktpl_search_score_rule_product';
    const RULE_ID = ScoreRuleInterface::ID;
    const STORE_ID = 'store_id';
    const PRODUCT_ID = 'product_id';
    const SCORE_FACTOR = ScoreRuleInterface::SCORE_FACTOR;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScoreRuleRepositoryInterface
     */
    private $scoreRuleRepository;

    /**
     * ScoreRuleIndexer constructor.
     *
     * @param ResourceConnection $resource
     * @param ScoreRuleRepositoryInterface $scoreRuleRepository
     */
    public function __construct(
        ResourceConnection $resource,
        ScoreRuleRepositoryInterface $scoreRuleRepository
    ) {
        $this->resource = $resource;
        $this->scoreRuleRepository = $scoreRuleRepository;
    }

    /**
     * Execute fully
     *
     * @throws \Zend_Db_Exception
     */
    public function executeFull()
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, []);
        }

        $this->executeZeroRule([]);
    }

    /**
     * Execute list
     *
     * @param array $ids
     * @throws \Zend_Db_Exception
     */
    public function executeList(array $ids)
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, $ids);
        }

        $this->executeZeroRule($ids);
    }

    /**
     * Execute row
     *
     * @param int $id
     * @throws \Zend_Db_Exception
     */
    public function executeRow($id)
    {
        foreach ($this->scoreRuleRepository->getCollection() as $scoreRule) {
            $this->execute($scoreRule, [$id]);
        }

        $this->executeZeroRule([$id]);
    }

    /**
     * Execute
     *
     * @param ScoreRuleInterface $scoreRule
     * @param array $productIds
     * @throws \Zend_Db_Exception
     */
    public function execute(ScoreRuleInterface $scoreRule, array $productIds)
    {
        $connection = $this->resource->getConnection();

        $this->ensureIndexTable();

        // Real Score Rules
        foreach ($scoreRule->getStoreIds() as $storeId) {
            $storeId = intval($storeId);
            $deleteWhere = [
                self::STORE_ID . ' = ' . $storeId,
                self::RULE_ID . ' = ' . $scoreRule->getId(),
            ];
            if ($productIds) {
                $deleteWhere[] = self::PRODUCT_ID . ' IN(' . implode(',', $productIds) . ')';
            }

            $connection->delete($this->getIndexTable(), $deleteWhere);

            $idx = 0;
            $ids = $scoreRule->getRule()->getMatchingProductIds($productIds);

            $scoreFactors = $this->getScoreFactors($scoreRule, $ids);

            do {
                $rows = [];

                for (; $idx < count($ids); $idx++) {
                    $row = [
                        self::RULE_ID      => $scoreRule->getId(),
                        self::STORE_ID     => $storeId,
                        self::PRODUCT_ID   => $ids[$idx],
                        self::SCORE_FACTOR => $scoreFactors[$ids[$idx]],
                    ];

                    $rows[] = $row;

                    if (count($rows) > 1000) {
                        break;
                    }
                }

                if (count($rows)) {
                    $connection->insertMultiple($this->getIndexTable(), $rows);
                }
            } while (count($rows));
        }
    }

    /**
     * Execute zero rule
     *
     * @param array $productIds
     * @throws \Zend_Db_Exception
     */
    private function executeZeroRule(array $productIds)
    {
        $connection = $this->resource->getConnection();

        $this->ensureIndexTable();

        $deleteWhere = [
            self::STORE_ID . ' = 0',
            self::RULE_ID . ' = 0',
        ];
        if ($productIds) {
            $deleteWhere[] = self::PRODUCT_ID . ' IN(' . implode(',', $productIds) . ')';
        }

        $connection->delete($this->getIndexTable(), $deleteWhere);

        // Product Search Weight
        $select = $connection->select()->from(
            [$this->resource->getTableName('catalog_product_entity')],
            ['entity_id', 'ktpl_search_weight']
        )->where('(ktpl_search_weight > 0 or ktpl_search_weight < 0)');
        if ($productIds) {
            $select->where('entity_id IN(' . implode(',', $productIds) . ')');
        }

        $data = $connection->fetchAll($select);
        $idx = 0;

        do {
            $rows = [];
            for (; $idx < count($data); $idx++) {
                $id = $data[$idx]['entity_id'];
                $factor = $data[$idx]['ktpl_search_weight'];
                $row = [
                    self::RULE_ID      => 0,
                    self::STORE_ID     => 0,
                    self::PRODUCT_ID   => $id,
                    self::SCORE_FACTOR => $factor > 0 ? '+' . $factor : $factor,
                ];

                $rows[] = $row;

                if (count($rows) > 1000) {
                    break;
                }
            }

            if (count($rows)) {
                $connection->insertMultiple($this->getIndexTable(), $rows);
            }
        } while (count($rows));
    }

    /**
     * Get score factors
     *
     * @param ScoreRuleInterface $scoreRule
     * @param array $productIds
     * @return array
     */
    private function getScoreFactors(ScoreRuleInterface $scoreRule, array $productIds)
    {
        list($sign, $factor, $relatively) = explode('|', $scoreRule->getScoreFactor());

        $result = [];

        if ($relatively === ScoreFactorRelatively::RELATIVELY_POPULARITY) {
            foreach ($productIds as $productId) {
                $result[$productId] = '+0';
            }

            $connection = $this->resource->getConnection();
            $select = $connection->select()->from(
                $this->resource->getTableName('sales_order_item'),
                ['product_id', 'cnt' => new \Zend_Db_Expr('count(*)')]
            )->group('product_id');
            $rows = $connection->fetchAll($select);
            $max = 0;
            foreach ($rows as $row) {
                if ($row['cnt'] > $max) {
                    $max = $row['cnt'];
                }
            }
            foreach ($rows as $row) {
                $result[$row['product_id']] = $sign . ($row['cnt'] / $max) * $factor;
            }
        } elseif ($relatively == ScoreFactorRelatively::RELATIVELY_RATING) {
            foreach ($productIds as $productId) {
                $result[$productId] = '+0';
            }

            $connection = $this->resource->getConnection();
            $select = $connection->select()->from(
                $this->resource->getTableName('rating_option_vote'),
                ['product_id' => 'entity_pk_value', 'cnt' => new \Zend_Db_Expr('avg(percent)')]
            )->group('product_id');
            $rows = $connection->fetchAll($select);
            $max = 100;

            foreach ($rows as $row) {
                $result[$row['product_id']] = $sign . ($row['cnt'] / $max) * $factor;
            }
        } else {
            foreach ($productIds as $productId) {
                $result[$productId] = $sign . $factor;
            }
        }

        return $result;
    }

    /**
     * Ensure index table
     *
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function ensureIndexTable()
    {
        $tableName = $this->getIndexTable();

        $connection = $this->resource->getConnection();

        if ($connection->isTableExists($tableName)) {
            return $this;
        }

        $table = $connection->newTable($tableName);

        $table->addColumn(self::RULE_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::STORE_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::PRODUCT_ID, Table::TYPE_INTEGER);
        $table->addColumn(self::SCORE_FACTOR, Table::TYPE_TEXT);

        $connection->createTable($table);

        return $this;
    }

    /**
     * Get index table
     *
     * @return string
     */
    private function getIndexTable()
    {
        return $this->resource->getTableName(ScoreRuleInterface::INDEX_TABLE_NAME);
    }
}
