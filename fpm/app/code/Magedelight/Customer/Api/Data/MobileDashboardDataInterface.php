<?php

/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Customer
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Customer\Api\Data;

/**
 * @api
 */
interface MobileDashboardDataInterface
{

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface[] Array of collection items.
     * @return $this
     */
    public function setRecentOrders(array $recentOrders = null);

   /**
    * @return \Magento\Sales\Api\Data\OrderInterface[] Array of collection items.
    */
    public function getRecentOrders();

   /**
    * @param \Magento\Customer\Api\Data\CustomerInterface $customerDetails
    * @return $this
    */
    public function setCustomerDetail($customerDetails);

   /**
    * @return \Magento\Customer\Api\Data\CustomerInterface $customerDetails
    */
    public function getCustomerDetail();

   /**
    * @param \Magedelight\Review\Api\Data\ReviewCollectionInterface $reviewData
    * @return $this
    */
    public function setCustomerReviews($reviewData);

   /**
    * @return \Magedelight\Review\Api\Data\ReviewCollectionInterface $reviewData
    */
    public function getCustomerReviews();

   /**
    * @param \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterface $vendorReviewData
    * @return $this
    */
    public function setVendorReviews($vendorReviewData);

   /**
    * @return \Magedelight\Vendor\Api\Data\VendorRatingCollectionInterface $vendorReviewData
    */
    public function getVendorReviews();
}
