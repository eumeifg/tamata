<?php

namespace Ktpl\DealsZone\Model;

use Ktpl\DealsZone\Api\DealsZoneManagementInterface;
use Magento\Catalog\Api\CategoryListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class DealsZoneManagement implements DealsZoneManagementInterface
{
    /**
     * @var CategoryListInterface
     */
    private $categoryList;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        CategoryListInterface $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ktpl\DealsZone\Api\Data\DealZoneSearchResultsInterfaceFactory $dealZoneSearchResults
    )
    {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->dealZoneSearchResults = $dealZoneSearchResults;
    }

    /*public function getDealsZone()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'is_deal_zone',
            1,
            "eq"
        )->create();

        return $this->categoryList->getList($searchCriteria);
    }*/

    public function getDealsZone($storeId = null)
    {
        if ($storeId != null) {
            $this->storeManager->setCurrentStore($storeId);
        }

        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        $collection = $this->_categoryCollectionFactory->create()
            ->addAttributeToSelect(
                ['entity_id', 'name', 'image', 'url_key', 'url_path', 'thumbnail','discount_label']
            )
            ->addExpressionAttributeToSelect('dealzone_image','CONCAT("'.$mediaUrl.'","",{{dealzone_image}})', ['dealzone_image'])
            ->addAttributeToFilter('is_deal_zone', '1')
            ->setStore($store)
            ->setOrder('entity_id', 'ASC');
        $searchResults = $this->dealZoneSearchResults->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;

        /*foreach ($collection as $dealCategory)
        {

        }*/
    }
}
