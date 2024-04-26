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
namespace Magedelight\Vendor\Api;

/**
 * Interface for managing vendors Reviews.
 */
/**
 * @api
 */
interface VendorReviewRepositoryInterface
{
    /**
     * Post vendor review.
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterface $vendorReview
     * @return \Magedelight\Vendor\Api\Data\VendorReviewInterface
     */
    public function save(
        \Magedelight\Vendor\Api\Data\VendorReviewInterface $vendorReview
    );

    /**
     * Get Vendor Reviews by Customer Id.
     * @param int $customerId
     * @param int|null $limit
     * @param int|null $currPage
     * @return \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterface $vendorReviewData
     */
    public function getByCustomerId($customerId, $limit = null, $currPage = null);

    /**
     * Get Vendor Reviews by Vendor Id.
     * @param int $vendorId
     * @param int|null $limit
     * @param int|null $currPage
     * @return \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterface $vendorReviewData
     */
    public function getByVendorId($vendorId, $limit = null, $currPage = null);

    /**
     * Get Vendor Ratings by Rating Id.
     * @param int $ratingId
     * @return \Magedelight\Vendor\Api\Data\VendorRatingDataInterface[] $vendorReviewData
     */
    public function getByRatingId($ratingId);
}
