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

namespace Ktpl\ElasticSearch\Index\Magento\Catalog\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Ktpl\ElasticSearch\Api\Service\ScoreServiceInterface;
use Ktpl\ElasticSearch\Service\ScoreRuleService;

/**
 * Class ScoreRulePlugin
 *
 * @package Ktpl\ElasticSearch\Index\Magento\Catalog\Product
 */
class ScoreRulePlugin
{
    /**
     * @var bool
     */
    private static $isApplied = false;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScoreRuleService
     */
    private $scoreRuleService;

    /**
     * ScoreRulePlugin constructor.
     *
     * @param ResourceConnection $resource
     * @param ScoreRuleService $scoreRuleService
     */
    public function __construct(
        ResourceConnection $resource,
        ScoreRuleService $scoreRuleService
    ) {
        $this->resource = $resource;
        $this->scoreRuleService = $scoreRuleService;
    }

    /**
     * Store documents
     *
     * @param object $storage
     * @param Table $table
     * @return Table
     */
    public function afterStoreApiDocuments($storage, Table $table)
    {
        // Apply only once for first index
        if (self::$isApplied) {
            return $table;
        }

        self::$isApplied = true;

        $this->scoreRuleService->applyScores($table);

        $select = $this->resource->getConnection()->select()
            ->from($table->getName(), ['*'])
            ->order('score desc');

        return $storage->storeDocumentsFromSelect($select);
    }
}
