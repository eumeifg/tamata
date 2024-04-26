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

use Ktpl\ElasticSearch\Api\Repository\IndexRepositoryInterface;
use Ktpl\ElasticSearch\Api\Service\IndexServiceInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;

/**
 * Class IndexService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class IndexService implements IndexServiceInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    protected $indexRepository;

    /**
     * IndexService constructor.
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
     * Get search collection
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection(IndexInterface $index)
    {
        return $this->indexRepository->getInstance($index)->getSearchCollection();
    }

    /**
     * Get query response
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse(IndexInterface $index)
    {
        return $this->indexRepository->getInstance($index)->getQueryResponse();
    }
}
