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

namespace Ktpl\ElasticSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Ktpl\ElasticSearch\Service\StemmingService;
use Magento\Framework\DB\Helper as DbHelper;

/**
 * Class Suggestion
 *
 * @package Ktpl\ElasticSearch\Block
 */
class Suggestion extends Template
{
    /**
     * @var QueryFactory
     */
    protected $searchQueryFactory;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var StemmingService
     */
    private $stemmingService;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * Suggestion constructor.
     *
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param Context $context
     * @param QueryFactory $queryFactory
     * @param DbHelper $dbHelper
     * @param StemmingService $stemmingService
     */
    public function __construct(
        QueryCollectionFactory $queryCollectionFactory,
        Context $context,
        QueryFactory $queryFactory,
        DbHelper $dbHelper,
        StemmingService $stemmingService
    )
    {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->searchQueryFactory = $queryFactory;
        $this->dbHelper = $dbHelper;
        $this->stemmingService = $stemmingService;

        parent::__construct($context);
    }

    /**
     * List of enabled indexes
     *
     * @return \Magento\Search\Model\Query[]|\Magento\Search\Model\ResourceModel\Query\Collection
     */
    public function getSuggestedTerms()
    {
        return $this->getSuggestedData();
    }

    /**
     * Get suggested data.
     *
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    private function getSuggestedData()
    {
        $query = $this->searchQueryFactory->get();
        $queryText = $this->stemmingService->singularize($query->getQueryText());
        $collection = $this->queryCollectionFactory->create();

        $collection->getSelect()->reset(\Magento\Framework\DB\Select::FROM)
            ->distinct(true)
            ->from(['main_table' => $collection->getTable('search_query')])
            ->where('num_results > 0')
            ->where('display_in_terms = 1')
            ->where('query_text LIKE ?', $this->dbHelper->addLikeEscape($queryText, ['position' => 'any']))
            ->where('query_text NOT LIKE ?', $this->dbHelper->addLikeEscape('%', ['position' => 'any']))
            ->order('popularity ' . \Magento\Framework\DB\Select::SQL_DESC);

        $collection->addFieldToFilter('query_text', ['nin' => [$query->getQueryText(), $queryText]])
            ->addStoreFilter([$this->_storeManager->getStore()->getId()])
            ->setOrder('popularity')
            ->setPageSize(10)
            ->distinct(true);

        return $collection;
    }
}
