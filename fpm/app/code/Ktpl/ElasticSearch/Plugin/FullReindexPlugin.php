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

namespace Ktpl\ElasticSearch\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;
use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;

/**
 * Class FullReindexPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class FullReindexPlugin
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    /**
     * FullReindexPlugin constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    )
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Save index
     *
     * @param Fulltext $fulltext
     * @param \Closure $proceed
     * @param null $scope
     * @return mixed
     */
    public function aroundExecuteFull(
        Fulltext $fulltext,
        \Closure $proceed,
        $scope = null
    )
    {
        $result = $proceed($scope);

        foreach ($this->indexRepository->getCollection() as $index) {
            if ($index->getIsActive()) {
                if ($index->getIdentifier() == 'catalogsearch_fulltext') {
                    $index->setStatus(IndexInterface::STATUS_READY);
                    $this->indexRepository->save($index);
                } else {
                    $this->indexRepository->getInstance($index)->reindexAll();
                }
            }
        }

        return $result;
    }
}
