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

namespace Ktpl\SearchAutocomplete\Api\Data;

/**
 * Interface IndexProviderInterface
 *
 * @package Ktpl\SearchAutocomplete\Api\Data
 */
interface IndexProviderInterface
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
     * @return $this
     */
    public function getCollection($index);

    /**
     * Get query response
     *
     * @param IndexInterface $index
     * @return \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index);
}
