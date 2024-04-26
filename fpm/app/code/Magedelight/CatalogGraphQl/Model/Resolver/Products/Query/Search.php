<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Model\Config;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResult;
use Magento\CatalogGraphQl\Model\Resolver\Products\SearchResultFactory;
use Magento\Search\Api\SearchInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\FieldSelection;

/**
 * Full text search for catalog using given search criteria.
 */
class Search extends \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search
{
    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var \Magento\Search\Model\Search\PageSizeProvider
     */
    private $pageSizeProvider;

    /**
     * @var SearchCriteriaInterfaceFactory
     */
    private $searchCriteriaFactory;

    /**
     * @var FieldSelection
     */
    private $fieldSelection;

    /**
     * @var ProductSearch
     */
    private $productsProvider;
    /**
     * @var Config
     */
    private $catalogConfig;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    private $queryFactory;
    
    private $catalogSearchHelper;

    private $storeManager;

    /**
     * @param SearchInterface $search
     * @param SearchResultFactory $searchResultFactory
     * @param \Magento\Search\Model\Search\PageSizeProvider $pageSize
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param FieldSelection $fieldSelection
     * @param ProductSearch $productsProvider
     * @param Config $catalogConfig
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        SearchInterface $search,
        SearchResultFactory $searchResultFactory,
        \Magento\Search\Model\Search\PageSizeProvider $pageSize,
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        FieldSelection $fieldSelection,
        ProductSearch $productsProvider,
        Config $catalogConfig,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->search = $search;
        $this->searchResultFactory = $searchResultFactory;
        $this->pageSizeProvider = $pageSize;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->fieldSelection = $fieldSelection;
        $this->productsProvider = $productsProvider;
        $this->catalogConfig = $catalogConfig;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->queryFactory = $queryFactory;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Return results of full text catalog search of given term, and will return filtered results if filter is specified
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param ResolveInfo $info
     * @return SearchResult
     * @throws \Exception
     */
    public function getResult(
        SearchCriteriaInterface $searchCriteria,
        ResolveInfo $info,
        array $value = null        
    ): SearchResult {
        $queryFields = $this->fieldSelection->getProductsFieldSelection($info);
        $realPageSize = $searchCriteria->getPageSize();
        $realCurrentPage = $searchCriteria->getCurrentPage();

        // Current page must be set to 0 and page size to max for search to grab all ID's as temporary workaround
        $pageSize = $this->pageSizeProvider->getMaxPageSize();
        $searchCriteria->setPageSize($pageSize);
        $searchCriteria->setCurrentPage(0);
        $sortOrder = $searchCriteria->getSortOrders();
        $sortOrderNew = [];
        $searchCriteria->setSortOrders($sortOrderNew);
        $itemsResults = $this->search->search($searchCriteria);
        if (empty($sortOrder)) {
            $sortOrder[] = $this->sortOrderBuilder->setField($this->catalogConfig->getProductListDefaultSortBy())->setDirection('DESC')->create();
        }
        /* for get min max price range of b/w whole product collection */
        
            $searchCriteriaCopyForPriceFilter = $this->searchCriteriaFactory->create()
                ->setSortOrders($sortOrder)
                ->setCurrentPage(0);

            $searchResultsForPriceFilter = $this->productsProvider->getList($searchCriteriaCopyForPriceFilter, $itemsResults, $queryFields);
  
            foreach ($searchResultsForPriceFilter->getItems() as $product) {


                    if( (float)$product->getData('min_price') !=  (float)$product->getData('final_price') ){
                         
                        $price = $product->getData('min_price');
                    }else{
                         
                        $price = $product->getData('final_price');
                    }
                
                // $prices[] = $product->getData('final_price');                
                $prices[] = $price;                
            }


            
                if(!empty($prices)){
                    $minPrice = min($prices);  
                    $maxPrice = max($prices);   
                }else{
                    $minPrice = "0.000000";  
                    $maxPrice = "0.000000";
                }

        /* for get min max price range of b/w whole product collection */
        //Create copy of search criteria without conditions (conditions will be applied by joining search result)
        $searchCriteriaCopy = $this->searchCriteriaFactory->create()
            ->setSortOrders($sortOrder)
            ->setPageSize($realPageSize)
            ->setCurrentPage($realCurrentPage);

        $searchResults = $this->productsProvider->getList($searchCriteriaCopy, $itemsResults, $queryFields);


        /* Product search using GraphQl (mobile) add search text in Search Terms ---Start */
           
         if (isset($value['search']) && !empty($value['search'])) {
                
             $query = $this->queryFactory->get();
             $storeId = $this->storeManager->getStore()->getId();           

             $query->setQueryText($value['search']);
              
             $queryText = $query->getQueryText();    

             $query->saveIncrementalPopularity();
             $query->saveNumResults($searchResults->getTotalCount());
             $query->setStoreId($storeId);
         }
         /* Product search using GraphQl (mobile) add search text in Search Terms ---End */

        //possible division by 0
        if ($realPageSize) {
            $maxPages = (int)ceil($searchResults->getTotalCount() / $realPageSize);
        } else {
            $maxPages = 0;
        }
        $searchCriteria->setPageSize($realPageSize);
        $searchCriteria->setCurrentPage($realCurrentPage);

        $productArray = [];
 
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($searchResults->getItems() as $product) {            
            
            $productArray[$product->getId()] = $product->getData();
            $productArray[$product->getId()]['model'] = $product;
        }    
        
        return $this->searchResultFactory->create(
            [
                'totalCount' => $searchResults->getTotalCount(),
                'productsSearchResult' => $productArray,
                'searchAggregation' => $itemsResults->getAggregations(),
                'pageSize' => $realPageSize,
                'currentPage' => $realCurrentPage,
                'totalPages' => $maxPages,                
                'minPrice' => $minPrice,
                'maxPrice' => $maxPrice,
            ]
        );
    }
}
