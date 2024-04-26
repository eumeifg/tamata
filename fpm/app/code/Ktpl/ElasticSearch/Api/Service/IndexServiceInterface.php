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

namespace Ktpl\ElasticSearch\Api\Service;

use Ktpl\ElasticSearch\Api\Data\IndexInterface;

/**
 * Interface IndexServiceInterface
 * @package Ktpl\ElasticSearch\Api\Service
 */
interface IndexServiceInterface
{
    /**
     * Get search collection
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Data\Collection
     */
    public function getSearchCollection(IndexInterface $index);

    /**
     * Get query response
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse(IndexInterface $index);
}
