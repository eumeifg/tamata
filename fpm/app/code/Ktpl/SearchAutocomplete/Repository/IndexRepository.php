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

namespace Ktpl\SearchAutocomplete\Repository;

use Ktpl\SearchAutocomplete\Api\Data\Index\InstanceInterface;
use Ktpl\SearchAutocomplete\Api\Data\IndexProviderInterface;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;

/**
 * Class IndexRepository
 *
 * @package Ktpl\SearchAutocomplete\Repository
 */
class IndexRepository implements IndexRepositoryInterface
{
    /**
     * @var IndexProviderInterface
     */
    private $indexProvider;

    /**
     * @var InstanceInterface[]
     */
    private $instances;

    /**
     * IndexRepository constructor.
     *
     * @param array $indexProviders
     * @param array $additionalProviders
     * @param array $instances
     */
    public function __construct(
        $indexProviders = [],
        $additionalProviders = [],
        array $instances = []
    )
    {
        if ($additionalProviders) {
            $this->indexProvider = current($additionalProviders);
        } else {
            $this->indexProvider = current($indexProviders);
        }

        $this->instances = $instances;
    }

    /**
     * Get indices
     *
     * @return \Ktpl\SearchAutocomplete\Api\Data\IndexInterface[]
     */
    public function getIndices()
    {
        return $this->indexProvider->getIndices();
    }

    /**
     * Get collection
     *
     * @param \Ktpl\SearchAutocomplete\Api\Data\IndexInterface $index
     * @return IndexProviderInterface|\Magento\Framework\Data\Collection
     */
    public function getCollection($index)
    {
        return $this->indexProvider->getCollection($index);
    }

    /**
     * Get query response
     *
     * @param \Ktpl\SearchAutocomplete\Api\Data\IndexInterface $index
     * @return false|\Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index)
    {
        return $this->indexProvider->getQueryResponse($index);
    }

    /**
     * Get identifier instance
     *
     * @param string $identifier
     * @return bool|InstanceInterface|mixed
     */
    public function getInstance($identifier)
    {
        foreach ($this->instances as $id => $instance) {
            if ($identifier == $id) {
                return $instance;
            }
        }

        return false;
    }
}
