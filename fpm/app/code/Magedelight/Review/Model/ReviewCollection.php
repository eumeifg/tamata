<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Review
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Review\Model;

use Magedelight\Review\Api\Data\ReviewCollectionInterface;

class ReviewCollection extends \Magento\Framework\DataObject implements ReviewCollectionInterface
{
    /**
     * Get Vendor Total Reviews
     *
     * @return \Magedelight\Review\Api\Data\ReviewDataInterface[] $reviewItems
     */
    public function getItems()
    {
        return $this->getData('vendor_review_items');
    }

    /**
     * Set Vendor Total Reviews
     *
     * @param \Magedelight\Review\Api\Data\ReviewDataInterface[] $reviewItems
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
        return $this->getData(ReviewCollectionInterface::TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setTotalReviews($totalReviews)
    {
        return $this->setData(ReviewCollectionInterface::TOTAL, $totalReviews);
    }

    /**
     * @inheritdoc
     */
    public function getHasMore()
    {
        return $this->getData(ReviewCollectionInterface::HASMORE);
    }

    /**
     * @inheritdoc
     */
    public function setHasMore($hasMore)
    {
        return $this->setData(ReviewCollectionInterface::HASMORE, $hasMore);
    }
}
