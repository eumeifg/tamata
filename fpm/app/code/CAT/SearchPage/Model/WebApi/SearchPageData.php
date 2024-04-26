<?php

namespace CAT\SearchPage\Model\WebApi;

use CAT\SearchPage\Model\ResourceModel\SearchPage\CollectionFactory;
use Ktpl\BannerManagement\Helper\Data as BannerHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\BannerManagement\Model\Config\Source\Image as ImageModel;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\CategoryFactory;

class SearchPageData extends \Magento\Framework\DataObject
{

     /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @type BannerHelper
     */
    protected $helperData;

    /**
     * @type DateTime
     */
    protected $date;

    /**
     * @type UserContextInterface
     */
    protected $userContext;

    /**
     * @type CustomerRepositoryInterface
     */
    protected $customerRepsitory;

    /**
     * @type BannerSearchResultsInterfaceFactory
     */
    protected $bannerSearchResult;

    /**
     * @type ImageModel
     */
    protected $imageModel;

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    /**
     * BannerSliders constructor.
     * @param CollectionFactory $collectionFactory
     * @param BannerHelper $helperData
     * @param DateTime $date
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepsitory
     * @param BannerSearchResultsInterfaceFactory $bannerSearchResult
     * @param ImageModel $imageModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        BannerHelper $helperData,
        DateTime $date,
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepsitory,
        BannerSearchResultsInterfaceFactory $bannerSearchResult,
        ImageModel $imageModel,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory
    ) {
        $this->collectionFactory        = $collectionFactory;
        $this->helperData                = $helperData;
        $this->date                      = $date;
        $this->userContext               = $userContext;
        $this->customerRepsitory         = $customerRepsitory;
        $this->bannerSearchResult        = $bannerSearchResult;
        $this->imageModel                = $imageModel;
        $this->storeManager              = $storeManager;
        $this->categoryFactory           = $categoryFactory;
    }

    /**
     * Load search data collection by given search criteria
     *
     * @return \CAT\SearchPage\Api\Data\SearchPageInterface[]
     */
    public function TopBanner()
    {
        
         $slider = $this->collectionFactory->create()->addFieldToFilter('status', 1);
         return $slider->getData();
    }

     /**
     * Load search data collection by given search criteria
     *
     * @return \CAT\SearchPage\Api\Data\SearchPageInterface[]
     */

    public function TopCategory()
    {
        $category = $this->collectionFactory->create()->addFieldToSelect('data_id')->addFieldToFilter('status', 1)->getFirstItem();
        $category = $this->categoryFactory->create()->load($category->getData());
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize(10)->addAttributeToSort('entity_id', 'DESC');
        return $categoryProducts->getData();
    }

     /**
     * Load search data collection by given search criteria
     *
     * @return \CAT\SearchPage\Api\Data\SearchPageInterface[]
     */

    public function BottomBanner()
    {
        $slider = $this->collectionFactory->create()->addFieldToFilter('status', 1);
        return $slider->getData();
    }

     /**
     * Load search data collection by given search criteria
     *
     * @return \CAT\SearchPage\Api\Data\SearchPageInterface[]
     */

    public function BottomCategory()
    {
        $category = $this->collectionFactory->create()->addFieldToSelect('data_id')->addFieldToFilter('status', 1)->getFirstItem();
        $category = $this->categoryFactory->create()->load($category->getData());
        $categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')->setPageSize(10)->addAttributeToSort('entity_id', 'DESC');
        return $categoryProducts->getData();
    }
}
