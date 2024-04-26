<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_BannerManagement
 * @copyright 2019 (c) KrishTechnolabs (https://www.krishtechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */
namespace Ktpl\BannerManagement\Model\Cms\HomePage;

use Ktpl\BannerManagement\Api\SliderRepositoryInterface;
use Ktpl\BannerManagement\Api\Data;
use Ktpl\BannerManagement\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Ktpl\BannerManagement\Helper\Data as BannerHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ktpl\BannerManagement\Model\Config\Source\Image as ImageModel;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Ktpl\BannerManagement\Api\Data\BannerSearchResultsInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

class Banners extends \Magento\Framework\DataObject
{

    /**
     * @var SliderCollectionFactory
     */
    protected $sliderCollectionFactory;

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
     * @param SliderCollectionFactory $sliderCollectionFactory
     * @param BannerHelper $helperData
     * @param DateTime $date
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepsitory
     * @param BannerSearchResultsInterfaceFactory $bannerSearchResult
     * @param ImageModel $imageModel
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SliderCollectionFactory $sliderCollectionFactory,
        BannerHelper $helperData,
        DateTime $date,
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepsitory,
        BannerSearchResultsInterfaceFactory $bannerSearchResult,
        ImageModel $imageModel,
        StoreManagerInterface $storeManager
    ) {
        $this->sliderCollectionFactory   = $sliderCollectionFactory;
        $this->helperData                = $helperData;
        $this->date                      = $date;
        $this->userContext               = $userContext;
        $this->customerRepsitory         = $customerRepsitory;
        $this->bannerSearchResult        = $bannerSearchResult;
        $this->imageModel                = $imageModel;
        $this->storeManager              = $storeManager;
    }

    /**
     * Load Slider data collection by given search criteria
     *
     * @return \Ktpl\BannerManagement\Api\Data\BannerInterface[]
     */
    public function build()
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
        $slider = $this->sliderCollectionFactory->create()
            ->addOrder('priority')
            ->addFieldToFilter('visible_devices', 1)
            ->addFieldToFilter('customer_group_ids', ['in' => $groupId])
            ->addFieldToFilter('store_ids', ['in' => [0, $this->storeManager->getStore()->getId()]])
            ->addFieldToFilter(['from_date', 'from_date'],[['null' => true],['lteq' => $this->date->date()]])
            ->addFieldToFilter(['to_date', 'to_date'],[['null' => true],['gteq' => $this->date->date()]])
            ->addFieldToSelect(['slider_id','visible_devices'])
            ->getFirstItem();

        $banners = $this->helperData->getBannerCollection(
            $slider->getSliderId(),
            ['name','image','url_banner','banner_text','page_type','data_id']
        )->addFieldToFilter('status', 1);

        $formattedBanners = [];
        if($banners->getSize() > 0){
            $formattedBanners = $banners->getData();
            foreach ($formattedBanners as &$banner) {
                $banner['image'] = $this->imageModel->getBaseUrl(). $banner['image'];
            }
        }

        return $formattedBanners;
    }
}
