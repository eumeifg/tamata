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
namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */

interface VendorRatingCollectionInterface
{
    const TOTAL = 'total';
    const HASMORE = 'hasmore';

    /**
     * Get Vendor Reviews
     *
     * @return \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $vendorItems
     */
    public function getItems();

    /**
     * Set Vendor Total Reviews
     *
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $vendorItems
     * @return $this
     */
    public function setItems($vendorItems);

    /**
     * Get Vendor Total Reviews
     *
     * @return int
     */
    public function getTotalReviews();

    /**
     * Set Vendor Total Reviews
     *
     * @param int $totalReviews
     * @return $this
     */
    public function setTotalReviews($totalReviews);
    
    /**
     * Get Has more
     * @return bool
     */
    public function getHasMore();

    /**
     * Set Has more
     * @param bool $hasMore
     * @return $this
     */
    public function setHasMore($hasMore);
}
