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

namespace Ktpl\SearchAutocomplete\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ktpl\SearchAutocomplete\Api\Data\Index\InstanceInterface;
use Ktpl\SearchAutocomplete\Api\Data\IndexInterface;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Class AbstractIndex
 *
 * @package Ktpl\SearchAutocomplete\Index
 */
abstract class AbstractIndex implements InstanceInterface
{
    /**
     * @var AbstractCollection
     */
    protected $collection;
    /**
     * @var IndexInterface
     */
    protected $index;
    /**
     * @var int
     */
    protected $limit = 10;
    /**
     * @var \Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    protected $queryResponse;
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * Set index
     *
     * @param IndexInterface $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Set limit
     *
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit > 0 ? $limit : 10;

        return $this;
    }

    /**
     * Set repository
     *
     * @param IndexRepositoryInterface $indexRepository
     * @return $this
     */
    public function setRepository(IndexRepositoryInterface $indexRepository)
    {
        $this->indexRepository = $indexRepository;

        return $this;
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * Get index collection
     *
     * @return \Magento\Framework\Data\Collection|AbstractCollection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->indexRepository->getCollection($this->index);
            $this->collection->setPageSize($this->limit);

            if (method_exists($this->collection, 'getSelect')) {
                if (strpos($this->collection->getSelect(), 'search_result') !== false) {
                    $this->collection->getSelect()->order('score desc');
                }
            }
        }

        return $this->collection;
    }

    /**
     * Get items
     *
     * @return array
     */
    abstract public function getItems();
}
