<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Product\DataProvider;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;

/**
 * Product field data provider for product search, used for GraphQL resolver processing.
 */
class ProductSearch extends \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionPreProcessor;

    /**
     * @var CollectionPostProcessor
     */
    private $collectionPostProcessor;

    /**
     * @var SearchResultApplierFactory;
     */
    private $searchResultApplierFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_conn;

    private $count;
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;
    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
     */
    private $collectionFilter;

    private $collection = false;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionPreProcessor
     * @param CollectionPostProcessor $collectionPostProcessor
     * @param SearchResultApplierFactory $searchResultsApplierFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\Category\CollectionFilter $collectionFilter
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionPreProcessor,
        CollectionPostProcessor $collectionPostProcessor,
        SearchResultApplierFactory $searchResultsApplierFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Category\CollectionFilter $collectionFilter,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($collectionFactory, $searchResultsFactory, $collectionPreProcessor, $collectionPostProcessor, $searchResultsApplierFactory);
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionPreProcessor = $collectionPreProcessor;
        $this->collectionPostProcessor = $collectionPostProcessor;
        $this->searchResultApplierFactory = $searchResultsApplierFactory;
        $this->layerResolver = $layerResolver->get();
        $this->collectionFilter = $collectionFilter;
        $this->_conn = $resource->getConnection('catalog');
    }

    /**
     * Get list of product data with full data set. Adds eav attributes to result set from passed in array
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultInterface $searchResult
     * @param array $attributes
     * @return SearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria,
        SearchResultInterface $searchResult,
        array $attributes = []
    ): SearchResultsInterface {
        /** @var Collection $collection */

        if(!$this->collection){
            $collection = $this->collectionFactory->create();
            $mcollection = $this->collectionFactory->create();

            //Join search results
            $this->getSearchResultsApplier($searchResult, $collection, $this->getSortOrderArray($searchCriteria))->apply();
            $this->getSearchResultsApplier($searchResult, $mcollection, $this->getSortOrderArray($searchCriteria))->apply();

            $category = $this->layerResolver->getCurrentCategory();
            $this->collectionFilter->filter($collection, $category);
            $this->collectionFilter->filter($mcollection, $category);
            $productids = $mcollection->getAllIds();
            $productIds = array_merge(
                $this->getProductCollectionwithQty($productids)->getAllIds(),
                $this->getProductCollectionwithconfifQty($productids)->getAllIds()
            );
            $mcollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $this->count = $mcollection->count();
            $this->collectionPreProcessor->process($collection, $searchCriteria, $attributes);
            if (array_keys($this->getSortOrderArray($searchCriteria))[0] === 'most_viewed') {
                $reportEventTable = $collection->getResource()->getTable('report_event');
                $subSelect = $this->_conn->select()->from(['report_event_table' => $reportEventTable], 'COUNT(report_event_table.event_id)')->where('report_event_table.object_id = e.entity_id');
                $collection->getSelect()->columns(['views' => $subSelect])->order('views ' . $this->getSortOrderArray($searchCriteria)['most_viewed'] );
            }
            if ($searchCriteria->getSortOrders()[0]->getField() === 'random') {
                $collection->getSelect()->orderRand();
            }
            if ($searchCriteria->getSortOrders()[0]->getField() === 'price') {
                $collection->getSelect()->order('price_index.min_price '  . $this->getSortOrderArray($searchCriteria)['price']);
            }
            $collection->getSelect()->order('e.entity_id DESC');
            if ($searchCriteria->getSortOrders()[0]->getField() === 'name') {
                $collection->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
            }
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $collection->load();
            if( $searchCriteria->getCurrentPage() != 0){
                $this->collection = $collection;
            }
        }
        else{
            $collection = $this->collection;
        }   
        $this->collectionPostProcessor->process($collection, $attributes);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount( $this->count);
        return $searchResults;
    }

    /**
     * Create searchResultApplier
     *
     * @param SearchResultInterface $searchResult
     * @param Collection $collection
     * @param array $orders
     * @return SearchResultApplierInterface
     */
    private function getSearchResultsApplier(
        SearchResultInterface $searchResult,
        Collection $collection,
        array $orders
    ): SearchResultApplierInterface {
        return $this->searchResultApplierFactory->create(
            [
                'collection' => $collection,
                'searchResult' => $searchResult,
                'orders' => $orders
            ]
        );
    }
    public function getProductCollectionwithconfifQty($productIDs)
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->join(
            ['vprodc' =>'md_vendor_product'],
            "e.entity_id = vprodc.parent_id"
        )->where('vprodc.qty > ?', 0);
        $collection->getSelect()->join(
            ['vprodc2' => 'md_vendor_product_website'],
            "vprodc.vendor_product_id = vprodc2.vendor_product_id"
        )->where('vprodc2.status = ?', 1);
        $collection->addFieldToFilter('entity_id', ['in' => $productIDs]);
        return $collection;
    }


    /**
     * Format sort orders into associative array
     *
     * E.g. ['field1' => 'DESC', 'field2' => 'ASC", ...]
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    private function getSortOrderArray(SearchCriteriaInterface $searchCriteria)
    {
        $ordersArray = [];
        $sortOrders = $searchCriteria->getSortOrders();
        if (is_array($sortOrders)) {
            foreach ($sortOrders as $sortOrder) {
                $ordersArray[$sortOrder->getField()] = $sortOrder->getDirection();
            }
        }

        return $ordersArray;
    }
    
    public function getProductCollectionwithQty($productIDs)
    {
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->join(
            ['vprodc' => 'md_vendor_product'],
            "e.entity_id = vprodc.marketplace_product_id"
        )->where('vprodc.qty > ?', 0);
        $collection->getSelect()->join(
            ['vprodc2' => 'md_vendor_product_website'],
            "vprodc.vendor_product_id = vprodc2.vendor_product_id"
        )->where('vprodc2.status = ?', 1);
        $collection->addFieldToFilter('entity_id', ['in' => $productIDs]);
        return $collection;
    }
}
