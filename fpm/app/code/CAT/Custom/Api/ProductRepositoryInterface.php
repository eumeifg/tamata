<?php

namespace CAT\Custom\Api;

interface ProductRepositoryInterface {

    /**
     * Get product list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \CAT\Custom\Api\Data\ProductSearchResultsInterface
     */
    public function getProductList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

}