<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class VendorReviewRepository implements \Magedelight\Vendor\Api\VendorReviewRepositoryInterface
{
    /**
     * @var Vendor[]
     */
    protected $instancesById = [];

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magedelight\Vendor\Model\ResourceModel\Vendor
     */
    protected $resourceModel;

    protected $reviewCount;

    protected $hasMore;
    /**
     * @var ResourceModel\Vendorfrontratingtype
     */
    protected $vendorFrontRatingTypeResource;

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var \Magento\Review\Model\Rating\Option
     */
    protected $_ratingOptions;

    /**
     * @var ResourceModel\Vendorrating
     */
    protected $_vendorRating;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var ResourceModel\Vendorrating\CollectionFactory
     */
    protected $vendorRatingCollection;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory
     */
    protected $vendorReviewInterface;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterfaceFactory
     */
    protected $vendorRatingInterfaceCollection;

    /**
     * @var VendorfrontratingtypeFactory
     */
    protected $frontRating;

    /**
     * @var \Magedelight\Vendor\Api\Data\VendorRatingDataInterfaceFactory
     */
    protected $vendorRatingInterface;

    /**
     * VendorReviewRepository constructor.
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\Rating\Option $ratingOptions
     * @param ResourceModel\Vendorrating $vendorRating
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ResourceModel\Vendorfrontratingtype $vendorFrontRatingTypeResource
     * @param ResourceModel\Vendorrating\CollectionFactory $vendorRatingCollection
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory $vendorReviewInterface
     * @param \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterfaceFactory $vendorRatingInterfaceCollection
     * @param VendorfrontratingtypeFactory $frontRating
     * @param \Magedelight\Vendor\Api\Data\VendorRatingDataInterfaceFactory $vendorRatingInterface
     */
    public function __construct(
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\Rating\Option $ratingOptions,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating $vendorRating,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magedelight\Vendor\Model\ResourceModel\Vendorfrontratingtype $vendorFrontRatingTypeResource,
        \Magedelight\Vendor\Model\ResourceModel\Vendorrating\CollectionFactory $vendorRatingCollection,
        \Magedelight\Vendor\Api\Data\VendorReviewInterfaceFactory $vendorReviewInterface,
        \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterfaceFactory $vendorRatingInterfaceCollection,
        \Magedelight\Vendor\Model\VendorfrontratingtypeFactory $frontRating,
        \Magedelight\Vendor\Api\Data\VendorRatingDataInterfaceFactory $vendorRatingInterface
    ) {
        $this->_ratingFactory         = $ratingFactory;
        $this->_ratingOptions         = $ratingOptions;
        $this->_vendorRating          = $vendorRating;
        $this->_storeManager          = $storeManager;
        $this->_date                  = $date;
        $this->vendorRatingCollection = $vendorRatingCollection;
        $this->vendorReviewInterface  = $vendorReviewInterface;
        $this->vendorRatingInterfaceCollection = $vendorRatingInterfaceCollection;
        $this->vendorFrontRatingTypeResource = $vendorFrontRatingTypeResource;
        $this->frontRating            = $frontRating;
        $this->vendorRatingInterface  = $vendorRatingInterface;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorReviewInterface $vendorReview)
    {
        $vendorReview->setStoreId($this->_storeManager->getStore()->getId());
        $vendorReview->setCreatedAt($this->_date->gmtDate());
        $vendorReview->getResource()->save($vendorReview);

        if (!$vendorReview->getVendorRatingId()) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('We were unable to save Review, Please Try again')
            );
        }

        $this->processVendorRatings($vendorReview);
        return $vendorReview;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getByCustomerId($customerId, $limit = null, $currPage = null)
    {
        $vendorRatingCol = $this->vendorRatingCollection->create();
        if ($currPage === null) {
            $currPage = 1;
        }

        if ($limit === null) {
            $limit = 10;
        }
        $vendorRatingCol->addFieldToFilter('customer_id', $customerId)
            ->setPageSize($limit)->setCurPage($currPage)
            ->setOrder('main_table.vendor_rating_id', 'ASC');
        $vendorRatingCol->getSelect()->group('main_table.vendor_rating_id');

        $vendorRatingCol->getSelect()->joinLeft(
            ['mdvwd'=>'md_vendor_website_data'],
            "main_table.vendor_id = mdvwd.vendor_id",
            [
                'mdvwd.name',
                'mdvwd.business_name'
            ]
        );
        $vendorRatingCol->getSelect()->joinLeft(
            ['mdrat'=>'md_vendor_rating_rating_type'],
            'main_table.vendor_rating_id = mdrat.vendor_rating_id ',
            ['SUM(rating_avg) as rating_avg']
        );

        $this->reviewCount = $vendorRatingCol->getSize();
        $total = $vendorRatingCol->getSize();

        $hasmoreData = true;

        if ($limit * $currPage >= $total) {
            $hasmoreData = false;
        }
        $this->hasMore = $hasmoreData;
        $vendorCollection = $vendorRatingCol->getData();
        $data = [];
        foreach ($vendorRatingCol as $vendorCol) {
            $vendor = $this->vendorReviewInterface->create();
            $data[] = $vendor->setData($vendorCol->getData());
        }

        $vendorRatings = $this->vendorRatingInterfaceCollection->create();
        $vendorRatings->setItems($data);
        $vendorRatings->setTotalReviews($total);
        $vendorRatings->setHasMore($hasmoreData);

        return $vendorRatings;
    }

    /**
     * {@inheritdoc}
     */
    public function getByVendorId($vendorId, $limit = null, $currPage = null)
    {
        $vendorRatingCol = $this->vendorRatingCollection->create();
        if ($currPage === null) {
            $currPage = 1;
        }

        if ($limit === null) {
            $limit = 10;
        }
        $vendorRatingCol->addFieldToFilter('main_table.vendor_id', $vendorId)
            ->addFieldToFilter('main_table.is_shared', 1)
            ->setPageSize($limit)->setCurPage($currPage)->setOrder('main_table.vendor_rating_id', 'ASC');
        $vendorRatingCol->getSelect()->group('main_table.vendor_rating_id');

        $vendorRatingCol->getSelect()->joinLeft(
            ['customer'=>'customer_entity'],
            "main_table.customer_id = customer.entity_id",
            [
                'CONCAT(customer.firstname," ",customer.lastname) AS customer_name'
            ]
        );
        $vendorRatingCol->getSelect()->joinLeft(
            ['order'=>'md_vendor_order'],
            "main_table.vendor_order_id = order.vendor_order_id",
            [
                'increment_id'
            ]
        );

        $vendorRatingCol->getSelect()->joinLeft(
            ['mdrat'=>'md_vendor_rating_rating_type'],
            'main_table.vendor_rating_id = mdrat.vendor_rating_id',
            ['SUM(rating_avg) as rating_avg']
        );

        $this->reviewCount = $vendorRatingCol->getSize();
        $total = $vendorRatingCol->getSize();

        $hasmoreData = true;

        if ($limit * $currPage >= $total) {
            $hasmoreData = false;
        }
        $this->hasMore = $hasmoreData;
        $vendorCollection = $vendorRatingCol->getData();
        $data = [];
        foreach ($vendorRatingCol as $vendorCol) {
            $vendor = $this->vendorReviewInterface->create();
            $ratingData = $this->getByRatingId($vendorCol->getVendorRatingId());
            if (!empty($ratingData)) {
                $vendorCol->setData('rating_options', $ratingData);
            }
            $data[] = $vendor->setData($vendorCol->getData());
        }

        $vendorRatings = $this->vendorRatingInterfaceCollection->create();
        $vendorRatings->setItems($data);
        $vendorRatings->setTotalReviews($total);
        $vendorRatings->setHasMore($hasmoreData);

        return $vendorRatings;
    }

    /**
     * Get Vendor Ratings by Rating Id.
     * @param int $ratingId
     * @return Magedelight\Vendor\Api\Data\VendorRatingDataInterface[] $vendorReviewData
     */
    public function getByRatingId($ratingId)
    {
        $vendorRating = $this->frontRating->create()->getCollection();

        $vendorRating->addFieldToFilter('vendor_rating_id', $ratingId);
        $vendorRating->getSelect()->joinLeft(
            ['mdratoption'=>'rating_option'],
            'main_table.option_id = mdratoption.option_id',
            ['rating_id']
        );

        $vendorRating->getSelect()->joinLeft(
            ['mdrating'=>'rating'],
            'mdratoption.rating_id = mdrating.rating_id',
            ['rating_code']
        );

        $data = [];
        foreach ($vendorRating as $rating) {
            $vendorRatingFactory = $this->vendorRatingInterface->create();
            $vendorRatingFactory->setData($rating->getData());
            $data[] = $vendorRatingFactory->getData();
        }

        return $data;
    }

    /**
     * Process Vendor Ratings
     *
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterface $vendorReview
     * @return \Magedelight\Vendor\Api\Data\VendorReviewInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function processVendorRatings($vendorReview)
    {
        try {
            if ($vendorReview->hasRatingOptions() && $vendorReview->getVendorRatingId()) {
                $vRatingId = $vendorReview->getVendorRatingId();
                $allRatCount = count($vendorReview->getRatingOptions());
                foreach ($vendorReview->getRatingOptions() as $ratingOption) {
                    $ratingOption->setData('vendor_rating_id', $vRatingId);
                    $rvalueFinal = (($ratingOption->getRatingValue() / 5) * 100) / $allRatCount;
                    $ratingOption->setData('rating_avg', $rvalueFinal);
                    $ratingOption->setData('store_id', $vendorReview->getStoreId());
                    $this->vendorFrontRatingTypeResource->save($ratingOption);
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save vendor Review'));
        }
        return $vendorReview;
    }
}
