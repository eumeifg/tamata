<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Api\Repository;

use Magento\Framework\Data\Collection;
use Ktpl\SearchAutocomplete\Api\Data\Index\InstanceInterface;
use Ktpl\SearchAutocomplete\Api\Data\IndexInterface;

/**
 * Interface IndexRepositoryInterface
 *
 * @package Ktpl\SearchAutocomplete\Api\Repository
 */
interface IndexRepositoryInterface
{
    /**
     * Get indices
     *
     * @return IndexInterface[]
     */
    public function getIndices();

    /**
     * Get index collection
     *
     * @param IndexInterface $index
     * @return Collection
     */
    public function getCollection($index);

    /**
     * Get query response
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface|false
     */
    public function getQueryResponse($index);

    /**
     * Get indentifier instance
     *
     * @param string $identifier
     * @return InstanceInterface
     */
    public function getInstance($identifier);
}
