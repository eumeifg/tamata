<?php

declare(strict_types=1);

namespace Magedelight\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Reviews implements ResolverInterface
{
    protected $_reviewCollectionFactory ;
    protected $_productRepository;
    protected $ratingFactory;
    protected $voteCollectionFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        RatingFactory $ratingFactory,
        VoteCollectionFactory $voteCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->_reviewCollectionFactory = $reviewCollectionFactory;
        $this->_productRepository= $productRepository;
        $this->ratingFactory = $ratingFactory;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product Product */
        $product = $value['model'];
        $productId = $product->getId();

        $collection = $this->_reviewCollectionFactory->create()
            ->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $productId
            )->setDateOrder();

       if($collection->getSize()){

            $sumOfAvgRating = 0;
            foreach ($collection as $key => $value) {
                $items[] = $this->getReviewData($value);                
            }
                           
            foreach ($items as $key => $value) {
                 $sumOfAvgRating += $value;
             } 

            $finalAvgRatings = ($sumOfAvgRating / $collection->getSize() );
                         
           $ratingSummaryCount = ((5*ceil($finalAvgRatings) )/100);
                
           $reviewsPercentCollection = number_format($ratingSummaryCount, 1);

       }else{
           $reviewsPercentCollection = 0;
       }
        return $reviewsPercentCollection;

    }

    public function getReviewData($review)
    {        
        return $review->getAverageRating() ?: $this->getReviewAverageRating($review->getId());
    }

    public function getReviewAverageRating($reviewId)
    {
        $reviewSummary = $this->ratingFactory->create()->getReviewSummary($reviewId);
        return (float)($reviewSummary->getData('sum') / $reviewSummary->getData('count'));
    }

    public function getReviewRatingsData($review)
    {
        $ratingData = [];

        $ratingVotes = is_array($review->getRatingVotes()) ? $review->getRatingVotes() : $this->getReviewRatingVotes($review);
        foreach ($ratingVotes as $ratingVote) {
            $ratingData[] = [
                'name' => $ratingVote->getRatingCode(),
                'percent' => $ratingVote->getPercent(),
                'value' => $ratingVote->getValue(),
            ];
        }
        return $ratingData;
    }

    public function getReviewRatingVotes($review)
    {
        $voteCollection = $this->voteCollectionFactory->create()
            ->setReviewFilter(
                $review->getId()
            )->setStoreFilter(
                $this->storeManager->getStore()->getId()
            )->addRatingInfo(
                $this->storeManager->getStore()->getId()
            )->load();
        $review->setRatingVotes($voteCollection);
        return $voteCollection;
    }
}
