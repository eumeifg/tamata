<?php

namespace Magedelight\Review\Model;

use Magedelight\Review\Api\ReviewInterface;
use Magedelight\Review\Api\Data\ReviewDataInterface;
use Magento\Review\Model\Review as magentReview;

class Review extends \Magento\Framework\DataObject implements ReviewInterface
{

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\CollectionFactory
     */
    protected $ratingCollectionFactory;

    /**
     * Review constructor.
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $reviewcollectionFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $rcollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magedelight\Review\Api\Data\ReviewDataInterfaceFactory $reviewDataInterface
     * @param \Magedelight\Review\Api\Data\ReviewCollectionInterfaceFactory $reviewcollectionInterface
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingCollectionFactory
     * @param \Magento\Review\Model\Rating\Option $ratingOptions
     * @param \Magedelight\Review\Api\Data\RatingDataInterfaceFactory $ratingDataInterface
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $reviewcollectionFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $rcollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magedelight\Review\Api\Data\ReviewDataInterfaceFactory $reviewDataInterface,
        \Magedelight\Review\Api\Data\ReviewCollectionInterfaceFactory $reviewcollectionInterface,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingCollectionFactory,
        \Magento\Review\Model\Rating\Option $ratingOptions,
        \Magedelight\Review\Api\Data\RatingDataInterfaceFactory $ratingDataInterface,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {
        $this->userContext = $userContext;
        $this->storeManager = $storeManager;
        $this->reviewcollectionFactory = $reviewcollectionFactory;
        $this->rcollectionFactory = $rcollectionFactory;
        $this->dateTime = $dateTime;
        $this->reviewDataInterface = $reviewDataInterface;
        $this->reviewcollectionInterface = $reviewcollectionInterface;
        $this->_ratingFactory = $ratingFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingOptions = $ratingOptions;
        $this->ratingDataInterface = $ratingDataInterface;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewsList($limit = null, $currPage = null, $storeId = null)
    {
        if ($storeId) {
            $this->storeManager->setCurrentStore($storeId);
        }
        $reviewcollection = $this->reviewcollectionFactory->create();
        $customerId = $this->userContext->getUserId();
        if ($limit === null) {
            $limit = 10;
        }
        if ($currPage === null) {
            $currPage = 1;
        }
        if (!$customerId) {
            throw new \Exception(__("Something Went Wrong,Please login and Try Again"));
        }

        $reviewcollection->addStoreFilter($this->storeManager->getStore()->getId())
                ->addCustomerFilter($customerId)
                ->setDateOrder();
        $reviewcollection->setPageSize($limit)->setCurPage($currPage);
        
        $reviewcollection->load()
                         ->addReviewSummary();
        $store = $this->storeManager->getStore();
        $total = $reviewcollection->getSize();
        $hasmoreData = true;
        if ($limit * $currPage >= $total) {
            $hasmoreData = false;
        }
        $reviewData = [];
        foreach ($reviewcollection as $_review) {
            $product = $this->_productRepositoryFactory->create()->getById($_review->getEntityId());
            $image = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getData('image');
            $reviewDataInt = $this->reviewDataInterface->create();
            $reviewDataInt->setProductId($_review->getEntityId());
            $reviewDataInt->setProductName($_review->getName());
            $reviewDataInt->setProductImage($image);
            $reviewDataInt->setNickName($_review->getNickname());
            if (array_key_exists('sum', $_review->getData()) && array_key_exists('count', $_review->getData())) {
                $reviewDataInt->setRatingAvg($_review->getSum() / $_review->getCount());
            } else {
                $reviewDataInt->setRatingAvg(0);
            }
            $reviewDataInt->setReviewDate($this->dateTime->date('d M,Y', $_review->getReviewCreatedAt()));
            $reviewDataInt->setTitle($_review->getTitle());
            $reviewDataInt->setReviewDetail($_review->getDetail());
            $reviewData[] = $reviewDataInt->getData();
        }
        $reviewDetails = $this->reviewcollectionInterface->create();
        $reviewDetails->setItems($reviewData);
        $reviewDetails->setTotalReviews($total);
        $reviewDetails->setHasMore($hasmoreData);
       
        return $reviewDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductReviewsList($productId, $storeId = null, $currPage = null)
    {
        $limit = null;
        if ($limit === null) {
            $limit = 10;
        }
        if ($currPage === null) {
            $currPage = 1;
        }

        if ($storeId) {
            $this->storeManager->setCurrentStore($storeId);
        }

        if (!$productId) {
            throw new \Exception(__("Something Went Wrong,Please login and Try Again"));
        }

        $reviewcollection = $this->getReviewsCollection($productId, $limit, $currPage);
        $total = $reviewcollection->getSize();
        $hasmoreData = true;
        if ($limit * $currPage >= $total) {
            $hasmoreData = false;
        }
        $reviewData = [];
        foreach ($reviewcollection as $_review) {
            $reviewDataInt = $this->reviewDataInterface->create();
            $reviewDataInt->setTitle($_review->getTitle());
            $reviewDataInt->setNickName($_review->getNickname());
            $ratingData = [];
            if (count($_review->getRatingVotes())) :
                $ratingDataint = $this->ratingDataInterface->create();
                foreach ($_review->getRatingVotes() as $_vote) :
                    $ratingDataint->setRatingCode($_vote->getRatingCode());
                    $ratingDataint->setRatingValue($_vote->getPercent());
                    $ratingData[] = $ratingDataint->getData();
                endforeach;
            endif;

            $reviewDataInt->setReviewDate($this->dateTime->date('d M,Y', $_review->getCreatedAt()));
            $reviewDataInt->setReviewDetail($_review->getDetail());
            $reviewDataInt->setRatingData($ratingData);
            $reviewData[] = $reviewDataInt->getData();
        }
        $reviewDetails = $this->reviewcollectionInterface->create();
        $reviewDetails->setItems($reviewData);
        $reviewDetails->setTotalReviews($total);
        $reviewDetails->setHasMore($hasmoreData);

        return $reviewDetails;
    }

    /**
     * Get collection of reviews
     *
     * @return ReviewCollection
     */
    public function getReviewsCollection($productId, $limit, $currPage)
    {
        $this->_reviewsCollection = $this->rcollectionFactory->create()->addStoreFilter(
            $this->storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $productId
        )->setPageSize($limit)
        ->setCurPage($currPage)
        ->setDateOrder()
        ->addRateVotes();
        return $this->_reviewsCollection;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function writeReviews(\Magedelight\Review\Api\Data\ReviewDataInterface $reviewData)
    {
        $customerId = $this->userContext->getUserId();

        $data = [
            "nickname" => $reviewData->getNickname(),
            "title"    => $reviewData->getTitle(),
            "detail"   => $reviewData->getReviewDetail()
        ];

        if (empty($reviewData->getTitle())) {
            throw new InputException(__('Not a valid Title'));
        }

        $ratings = [];
        if (empty($reviewData->getRatingData())) {
            throw new InputException(__('Not a valid rating data'));
        }
        //map vote option id with the star value
        foreach ($reviewData->getRatingData() as $rating) {
            $ratings[$rating->getRatingId()] = $this->getVoteOption($rating->getRatingId(), $rating->getRatingValue());
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->get('Magento\Catalog\Model\Product')->load($reviewData->getProductId());
        if (!$product->getId()) {
            throw new NoSuchEntityException(__('Product doesn\'t exist'));
        }
        if (($product) && !empty($data)) {
            $review = $this->_reviewFactory->create()->setData($data);
            $review->unsetData('review_id');

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review
                        ->getEntityIdByCode(magentReview::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(magentReview::STATUS_PENDING)
                        ->setCustomerId($customerId)
                        ->setStoreId($reviewData->getStoreId())
                        ->setStores([$reviewData->getStoreId()])
                        ->save();
                    if (count($ratings)) {
                        foreach ($ratings as $ratingId => $optionId) {
                            $this->_ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($optionId, $product->getId());
                        }
                    }
                    $review->aggregate();
                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
    }

    /**
     * @param $ratingId
     * @param $value
     * @return int
     */
    private function getVoteOption($ratingId, $value)
    {
        $optionId = 0;
        $ratingOptionCollection = $this->_ratingOptions->getCollection()
            ->addFieldToFilter('rating_id', $ratingId)
            ->addFieldToFilter('value', $value);
        if (count($ratingOptionCollection)) {
            foreach ($ratingOptionCollection as $row) {
                $optionId = $row->getOptionId();
            }
        }
        return $optionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingCode($entityCode)
    {
        return $this->ratingCollectionFactory->create()
            ->addEntityFilter($entityCode)
            ->setActiveFilter()
            ->getData();
    }
}
