<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_SearchAutocomplete
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\SearchAutocomplete\Model;

use Magedelight\SearchAutocomplete\Api\SearchInterface as MdSearchInterface;

class Search implements MdSearchInterface
{
    /**
     * @var \Magento\Framework\Search\Request\Builder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $searchEngine;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magedelight\Catalog\Api\CategoryProductRepositoryInterface
     */
    protected $mdProductRepository;

    /**
     * @param \Magedelight\Catalog\Api\CategoryProductRepositoryInterface $mdProductRepository
     * @param \Magento\Framework\Search\Request\BuilderFactory $requestBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Search\Model\SearchEngine $searchEngine
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magedelight\Catalog\Api\CategoryProductRepositoryInterface $mdProductRepository,
        \Magento\Framework\Search\Request\BuilderFactory $requestBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->mdProductRepository = $mdProductRepository;
        $this->requestBuilder = $requestBuilder->create();
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->searchEngine = $searchEngine;
        $this->productVisibility = $productVisibility;
        $this->request = $request;
    }

    /**
     * @return string
     */
    protected function getPriceRangeCalculation()
    {
        return $this->scopeConfig->getValue(
            \Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function search(
        $query,
        \Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria = null
    ) {
        $this->requestBuilder->bindDimension('scope', $this->storeManager->getStore()->getId());
        $this->requestBuilder->bind('search_term', $query);
        $this->requestBuilder->bind('visibility', $this->productVisibility->getVisibleInSearchIds());
        $priceRangeCalculation = $this->getPriceRangeCalculation();
        if ($priceRangeCalculation) {
            $this->requestBuilder->bind('price_dynamic_algorithm', $priceRangeCalculation);
        }
        $this->requestBuilder->setRequestName('quick_search_container');
        $queryRequest = $this->requestBuilder->create();
        $queryResponse = $this->searchEngine->search($queryRequest);
        $relevanceData = [];
        foreach ($queryResponse as $document) {
            $data['entity_id'] = $document->getId();
            $data['score'] = $document->getCustomAttribute('score')->getValue();
            $relevanceData[] = $data;
        }
        $this->request->setParam('relevance',$relevanceData);
        $productList = $this->mdProductRepository->getList($searchCriteria, null, null);
        return $productList;
    }
}
