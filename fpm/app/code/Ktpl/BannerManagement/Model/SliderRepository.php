<?php
/*
 * php version 7.2.17
 */
namespace Ktpl\BannerManagement\Model;

use Ktpl\BannerManagement\Api\SliderRepositoryInterface;
use Ktpl\BannerManagement\Api\Data;
use Ktpl\BannerManagement\Model\ResourceModel\Slider as ResourceSlider;
use Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Ktpl\BannerManagement\Helper\Data as bannerHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Ktpl\BannerManagement\Api\BannerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Helper\Context;
use Ktpl\BannerManagement\Model\Config\Source\Image as configImage;

/**
 * Class SliderRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SliderRepository implements SliderRepositoryInterface
{
    /**
     * @var ResourceSlider
     */
    protected $resource;

    /**
     * @var SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var SliderCollectionFactory
     */
    protected $sliderCollectionFactory;

    /**
     * @var Data\SliderSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Ktpl\BannerManagement\Api\Data\SliderInterfaceFactory
     */
    protected $dataSliderFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    protected $_resource;
    protected $bannerModel;

    /**
     * @type \Ktpl\BannerManagement\Helper\Data
     */
    public $helperData;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $bannerRepositoryInterface;

    /**
     * @param ResourceSlider                           $resource
     * @param SliderFactory                            $sliderFactory
     * @param Data\SliderInterfaceFactory              $dataSliderFactory
     * @param SliderCollectionFactory                  $sliderCollectionFactory
     * @param Data\SliderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper                         $dataObjectHelper
     * @param DataObjectProcessor                      $dataObjectProcessor
     * @param bannerHelper                             $helperData
     */
    public function __construct(
        ResourceSlider $resource,
        SliderFactory $sliderFactory,
        \Ktpl\BannerManagement\Api\Data\SliderInterfaceFactory $dataSliderFactory,
        SliderCollectionFactory $sliderCollectionFactory,
        Data\SliderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor = null,
        \Magento\Framework\App\ResourceConnection $Resource,
        \Ktpl\BannerManagement\Model\Banner $bannerModel,
        bannerHelper $helperData,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BannerRepositoryInterface $bannerRepositoryInterface,
        DateTime $date,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepsitory,
        \Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterfaceFactory $bannerSearchResult,
        configImage $configImage
    ) {
        $this->resource                  = $resource;
        $this->sliderFactory             = $sliderFactory;
        $this->sliderCollectionFactory   = $sliderCollectionFactory;
        $this->searchResultsFactory      = $searchResultsFactory;
        $this->dataObjectHelper          = $dataObjectHelper;
        $this->dataSliderFactory         = $dataSliderFactory;
        $this->dataObjectProcessor       = $dataObjectProcessor;
        $this->collectionProcessor       = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->_resource                 = $Resource;
        $this->bannerModel               = $bannerModel;
        $this->helperData                = $helperData;
        $this->searchCriteriaBuilder     = $searchCriteriaBuilder;
        $this->bannerRepositoryInterface = $bannerRepositoryInterface;
        $this->date                      = $date;
        $this->httpContext               = $httpContext;
        $this->storeManager              = $storeManager;
        $this->userContext               = $userContext;
        $this->customerRepsitory         = $customerRepsitory;
        $this->bannerSearchResult        = $bannerSearchResult;
        $this->imageModel                = $configImage;
    }

    /**
     * Save Slider data
     *
     * @param  \Ktpl\BannerManagement\Api\Data\SliderInterface $slider
     * @return banner
     * @throws CouldNotSaveException
     */
    public function save(Data\SliderInterface $slider)
    {
        try {
            $this->resource->save($slider);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $slider;
    }

    /**
     * Load Slider data by given Slider Identity
     *
     * @param  int $sliderId
     * @return \Ktpl\BannerManagement\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($sliderId)
    {
        $slider = $this->sliderFactory->create();

        $sliderBanners = $this->getSliderBanners($sliderId);

        $this->resource->load($slider, $sliderId);
        if (!$slider->getSliderId()) {
            throw new NoSuchEntityException(__('The Slider with the "%1" ID doesn\'t exist.', $sliderId));
        }
        $slider->setBanners($sliderBanners);

        return $slider;
    }

    /**
     * Load Slider data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Ktpl\BannerManagement\Api\Data\SliderSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /**
         * @var \Ktpl\BannerManagement\Model\ResourceModel\Slider\Collection $collection
         */
     
        $customerId = $this->userContext->getUserId();
        
        $groupId[] = 0;
        
        if($customerId)
        {
            $customer = $this->customerRepsitory->getById($customerId);
            $groupId[] = $customer->getGroupId();
            
        }
        
        $collection = $this->sliderCollectionFactory->create()
            ->addOrder('priority')
            ->addFieldToFilter('customer_group_ids', ['in' => $groupId])
            ->addFieldToFilter(['from_date', 'from_date'],[['null' => true],['lteq' => $this->date->date()]])
            ->addFieldToFilter(['to_date', 'to_date'],[['null' => true],['gteq' => $this->date->date()]])
            ->addFieldToSelect(['slider_id','visible_devices']);


        $this->collectionProcessor->process($criteria, $collection);

        /**
         * @var Data\SliderSearchResultsInterface $searchResults
         */
        $items = $collection->getItems();
        foreach ($items as $item) {
            $banners = $this->helperData->getBannerCollection(
                $item->getSliderId(),
                ['name','image','url_banner','banner_text','page_type','data_id']
            )->addFieldToFilter('status', 1);
            $bannerResult = $this->bannerSearchResult->create();
            $bannerResult->setItems($banners->getData());
            $item->setData('banners',$bannerResult);
            $item->setData('banner_path',$this->imageModel->getBaseUrl());
        }
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Slider
     *
     * @param  \Ktpl\BannerManagement\Api\Data\SliderInterface $slider
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\SliderInterface $slider)
    {
        try {
            $this->resource->delete($slider);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Slider by given Slider Identity
     *
     * @param  string $sliderId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($sliderId)
    {
        return $this->delete($this->getById($sliderId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 102.0.0
     * @return     CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }

    /**
     * Load Banners for particular slider
     */
    public function getSliderBanners($sliderId)
    {
        $collection = $this->helperData->getBannerCollection($sliderId)->addFieldToFilter('status', 1);
        $sliderBannerIds = $collection->getAllIds();
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'banner_id',
            $sliderBannerIds,
            "in"
        )->create();
        $sliderProducts = $this->bannerRepositoryInterface->getList($searchCriteria);

        return $sliderProducts;
    }
}
