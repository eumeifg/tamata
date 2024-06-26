<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Ui\DataProvider\Advocate;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\Search\ReportingInterface;
use Aheadworks\Raf\Model\ResourceModel\AdvocateSummary\Report\FriendPurchases as FriendPurchasesResource;

/**
 * Class ListingDataProvider
 *
 * @package Aheadworks\Raf\Ui\DataProvider\Advocate
 */
class ListingDataProvider extends DataProvider
{
    /**
     * @var FriendPurchasesResource
     */
    private $friendPurchasesResource;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param FriendPurchasesResource $friendPurchasesResource
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        FriendPurchasesResource $friendPurchasesResource,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->friendPurchasesResource = $friendPurchasesResource;
    }

    /**
     * {@inheritdoc}
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = parent::searchResultToOutput($searchResult);
        $arrItems['topTotals'][] = $this->friendPurchasesResource->getTopTotals($this->getSearchCriteria());

        return $arrItems;
    }
}
