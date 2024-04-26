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

use Magedelight\Vendor\Api\Data\VendorRatingCollectionInterface;

class VendorRatingCollection extends \Magento\Framework\DataObject implements VendorRatingCollectionInterface
{
    /**
     * Get Vendor Total Reviews
     *
     * @return \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $vendorItems
     */
    public function getItems()
    {
        return $this->getData('vendor_review_items');
    }

    /**
     * Set Vendor Total Reviews
     *
     * @param \Magedelight\Vendor\Api\Data\VendorReviewInterface[] $vendorItems
     * @return $this
     */
    public function setItems($vendorItems)
    {
        return $this->setData('vendor_review_items', $vendorItems);
    }

    /**
     * @inheritdoc
     */
    public function getTotalReviews()
    {
        return $this->getData(VendorRatingCollectionInterface::TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setTotalReviews($totalReviews)
    {
        return $this->setData(VendorRatingCollectionInterface::TOTAL, $totalReviews);
    }

    /**
     * @inheritdoc
     */
    public function getHasMore()
    {
        return $this->getData(VendorRatingCollectionInterface::HASMORE);
    }

    /**
     * @inheritdoc
     */
    public function setHasMore($hasMore)
    {
        return $this->setData(VendorRatingCollectionInterface::HASMORE, $hasMore);
    }
}
