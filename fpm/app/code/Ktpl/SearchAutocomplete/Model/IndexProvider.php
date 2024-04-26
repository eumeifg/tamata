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

namespace Ktpl\SearchAutocomplete\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ktpl\SearchAutocomplete\Api\Data\IndexProviderInterface;
use Ktpl\SearchAutocomplete\Api\Data\IndexInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

/**
 * Class IndexProvider
 *
 * @package Ktpl\SearchAutocomplete\Model
 */
class IndexProvider implements IndexProviderInterface
{
    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * IndexProvider constructor.
     *
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param QueryFactory $queryFactory
     * @param LayerResolver $layerResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QueryCollectionFactory $queryCollectionFactory,
        QueryFactory $queryFactory,
        LayerResolver $layerResolver,
        StoreManagerInterface $storeManager
    )
    {
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Get indices
     *
     * @return array
     */
    public function getIndices()
    {
        $indices = [];

        $indices[] = new DataObject([
            'title' => __('Products')->__toString(),
            'identifier' => 'catalogsearch_fulltext',
        ]);

        $indices[] = new DataObject([
            'title' => __('Popular suggestions')->__toString(),
            'identifier' => 'magento_search_query',
        ]);

        $indices[] = new DataObject([
            'title' => __('Products in categories')->__toString(),
            'identifier' => 'magento_catalog_categoryproduct',
        ]);

        return $indices;
    }

    /**
     * Get collection
     *
     * @param IndexInterface $index
     * @return AbstractCollection
     * @throws \Exception
     */
    public function getCollection($index)
    {
        switch ($index->getIdentifier()) {
            case 'magento_search_query':

                return $this->getSearchQueryCollection();
                break;

            case 'catalogsearch_fulltext':

                return $this->layerResolver->get()->getProductCollection();
                break;

            case 'magento_catalog_categoryproduct':

                return $this->layerResolver->get()->getProductCollection();
                break;

            default:
                throw new \Exception("Undefined index");
                break;
        }
    }

    /**
     * Get search query collection
     *
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSearchQueryCollection()
    {
        $query = $this->queryFactory->get();
        return $this->queryCollectionFactory->create()
            ->setQueryFilter($query->getQueryText())
            ->addFieldToFilter('query_text', ['neq' => $query->getQueryText()])
            ->addStoreFilter([$this->storeManager->getStore()->getId()])
            ->setOrder('popularity')
            ->distinct(true);
    }

    /**
     * Get query response
     *
     * @param IndexInterface $index
     * @return bool|\Magento\Framework\Search\Response\QueryResponse|\Magento\Framework\Search\ResponseInterface
     */
    public function getQueryResponse($index)
    {
        return false;
    }
}
