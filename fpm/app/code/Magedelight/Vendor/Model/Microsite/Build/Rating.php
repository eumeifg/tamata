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
namespace Magedelight\Vendor\Model\Microsite\Build;

use Magedelight\Vendor\Model\ResourceModel\Vendorrating\CollectionFactory as RatingCollectionFactory;
use Magedelight\Vendor\Api\Data\MicrositeRatingDataInterface;
use Magedelight\Vendor\Api\Data\MicrositeTopReviewsInterfaceFactory;

/**
 * Build microsite rating data
 */
class Rating extends \Magento\Framework\DataObject
{

    const TOP_REVIEWS_LIMIT = 3;

    /**
     * @var RatingCollectionFactory
     */
    protected $ratingCollectionFactory;

    /**
     * @var \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterfaceFactory
     */
    protected $micrositeRatingDataInterfaceFactory;

    /**
     * @var MicrositeTopReviewsInterfaceFactory
     */
    protected $micrositeTopReviewsInterfaceFactory;

    /**
     * Rating constructor.
     * @param RatingCollectionFactory $ratingCollectionFactory
     * @param MicrositeTopReviewsInterfaceFactory $micrositeTopReviewsInterfaceFactory
     * @param \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterfaceFactory $micrositeRatingDataInterfaceFactory
     * @param array $data
     */
    public function __construct(
        RatingCollectionFactory $ratingCollectionFactory,
        MicrositeTopReviewsInterfaceFactory $micrositeTopReviewsInterfaceFactory,
        \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterfaceFactory $micrositeRatingDataInterfaceFactory,
        $data = []
    ) {
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->micrositeRatingDataInterfaceFactory = $micrositeRatingDataInterfaceFactory;
        $this->micrositeTopReviewsInterfaceFactory = $micrositeTopReviewsInterfaceFactory;
        parent::__construct($data);
        ($this->getData('top_reviews_limit')) ?
            $this->getData('top_reviews_limit'):$this->setData('top_reviews_limit', self::TOP_REVIEWS_LIMIT);
    }

    /**
     *
     * @param int $vendorId
     * @return \Magedelight\Vendor\Api\Data\MicrositeRatingDataInterface
     * @throws NoSuchEntityException
     */
    public function build($vendorId)
    {
        $rating = $this->micrositeRatingDataInterfaceFactory->create();
        if ($vendorId) {
            /* Set Reviews data. */
            $reviewsCollection = $this->getVendorReviewsCollection($vendorId);
            $negativeReviewsCount = $neutralReviewsCount = $positiveReviewsCount = $totalReviews = 0;
            $oneStar = $twoStar = $threeStar = $fourStar = $fiveStar = 0;
            $topReviews = [];
            if ($reviewsCollection && $reviewsCollection->getSize() > 0) {
                $totalReviews = $reviewsCollection->getSize();
                foreach ($reviewsCollection as $review) {
                    $avg_rating = $review['rating_avg'];
                    if (!empty($avg_rating)) {
                        /* Reviews counting based on stars*/
                        if ($avg_rating >= 20 && $avg_rating < 40) {
                            $oneStar++;
                        } elseif ($avg_rating >= 40 && $avg_rating < 60) {
                            $twoStar++;
                        } elseif ($avg_rating >= 60 && $avg_rating < 80) {
                            $threeStar++;
                        } elseif ($avg_rating >= 80 && $avg_rating < 100) {
                            $fourStar++;
                        } elseif ($avg_rating == 100) {
                            $fiveStar++;
                        }
                        /* Reviews counting based on stars*/
                        
                        if ($avg_rating >= 0 && $avg_rating <= 33) {
                            $negativeReviewsCount++;
                        } elseif ($avg_rating > 33 && $avg_rating <= 66) {
                            $neutralReviewsCount++;
                        } elseif ($avg_rating > 66 && $avg_rating <= 100) {
                            $positiveReviewsCount++;
                        }

                        if ($avg_rating >= 70) {
                            if (count($topReviews) <= (int)$this->getData('top_reviews_limit')) {
                                $topReviews[] = $this->micrositeTopReviewsInterfaceFactory->create()
                                    ->setAvgRating(($avg_rating * 5) / 100)
                                    ->setCustomerName($review['customer_name'])
                                    ->setComment($review['comment'])
                                    ->setCreatedAt(date("d M Y", strtotime($review['created_at'])));
                            }
                        }
                    }
                }
            }
            $data['positive_reviews_count'] = $positiveReviewsCount;
            $data['negative_reviews_count'] = $negativeReviewsCount;
            $data['neutral_reviews_count'] = $neutralReviewsCount;
            $data['positive_ratio'] = ($totalReviews > 0) ?
                number_format(($positiveReviewsCount / $totalReviews) * 100, 2, '.', '') : 0;

            /* Reviews counting based on stars*/
            $data['one_star_count'] = $oneStar;
            $data['two_star_count'] = $twoStar;
            $data['three_star_count'] = $threeStar;
            $data['four_star_count'] = $fourStar;
            $data['five_star_count'] = $fiveStar;

            /* Reviews counting based on stars*/

            $data['top_reviews'] = $topReviews;

            $rating->setData($data);

            /* Set Reviews data. */
        }
        return $rating;
    }

    /**
     * @param $vendorId
     * @return \Magedelight\Vendor\Model\ResourceModel\Vendorrating\Collection
     */
    protected function getVendorReviewsCollection($vendorId)
    {
        $collection = $this->ratingCollectionFactory->create()
            ->addFieldToFilter('vendor_id', $vendorId)->addFieldToFilter('is_shared', 1);
        $collection->getSelect()->joinLeft(
            ['customer' => 'customer_entity'],
            "main_table.customer_id = customer.entity_id",
            [
                "CONCAT(firstname, ' ', lastname) as customer_name"

            ]
        );
        $collection->getSelect()->joinLeft(
            ['rvrt' => 'md_vendor_rating_rating_type'],
            "main_table.vendor_rating_id = rvrt.vendor_rating_id",
            [
                'ROUND(SUM(`rvrt`.`rating_avg`)) as rating_avg'

            ]
        )->group('main_table.vendor_rating_id')->order('main_table.created_at DESC');
        return $collection;
    }
}
