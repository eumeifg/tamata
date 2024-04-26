<?php
/*
 * php version 7.2.17
 */
namespace Ktpl\TopCategory\Model;

use Ktpl\TopCategory\Api\TopCategoryRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class TopCategoryRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TopCategoryRepository implements TopCategoryRepositoryInterface
{

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Ktpl\TopCategory\Api\Data\TopCategorySearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Load Top Category data collection by given search criteria
     * @return \Ktpl\TopCategory\Api\Data\TopCategorySearchResultsInterface
    */
    public function getTopCategoryList($storeId = null)
    {
        if ($storeId != null) {
            $this->storeManager->setCurrentStore($storeId);
        }

        $store = $this->storeManager->getStore();
        $mediaUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
        $collection = $this->_categoryCollectionFactory->create()
                            ->addAttributeToSelect(['entity_id', 'name', 'image', 'url_key', 'url_path'])
                            ->addExpressionAttributeToSelect('thumbnail','CONCAT("'.$mediaUrl.'","",{{thumbnail}})', ['thumbnail'])
                            ->setStore($store)
                            ->addAttributeToFilter('is_top_category', '1')
                            ->setOrder('entity_id', 'ASC');
        //$collection->getSelect()->getConcatSql(['$mediaUrl', 'thumbnail'], ' ');


        //echo $collection->getSelect()->__toString();die;
        /**
         * @var Data\TopCategorySearchResultsInterface $searchResults
         */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

}
