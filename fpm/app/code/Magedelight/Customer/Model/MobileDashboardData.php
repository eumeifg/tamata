<?php
/*
 * Copyright © 2016 Rocket Bazaar. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Magedelight\Customer\Model;

use Magedelight\Customer\Api\Data\MobileDashboardDataInterface;

class MobileDashboardData extends \Magento\Framework\DataObject implements MobileDashboardDataInterface
{
  
   /**
    * {@inheritdoc}
    */
    public function setRecentOrders(array $recentOrders = null)
    {
        return $this->setData('recent_orders', $recentOrders);
    }

   /**
    * {@inheritdoc}
    */
    public function getRecentOrders()
    {
        return $this->getData('recent_orders');
    }

   /**
    * {@inheritdoc}
    */
    public function setCustomerDetail($customerDetails)
    {
        return $this->setData('customer_detail', $customerDetails);
    }

   /**
    * {@inheritdoc}
    */
    public function getCustomerDetail()
    {
        return $this->getData('customer_detail');
    }

   /**
    * {@inheritdoc}
    */
    public function setCustomerReviews($reviewData)
    {
        return $this->setData('customer_review', $reviewData);
    }

   /**
    * {@inheritdoc}
    */
    public function getCustomerReviews()
    {
        return $this->getData('customer_review');
    }

   /**
    * {@inheritdoc}
    */
    public function setVendorReviews($vendorReviewData)
    {
        return $this->setData('vendor_reviews', $vendorReviewData);
    }

   /**
    * {@inheritdoc}
    */
    public function getVendorReviews()
    {
        return $this->getData('vendor_reviews');
    }
}
