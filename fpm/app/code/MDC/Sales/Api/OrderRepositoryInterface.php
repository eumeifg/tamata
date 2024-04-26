<?php

namespace MDC\Sales\Api;

/**
 * @api
 */
interface OrderRepositoryInterface {

    /**
     * Lists orders that match specified search criteria.
     * 
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \MDC\Sales\Api\Data\OrderSearchResultInterface Order search result interface.
     */
    public function getOrderList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}