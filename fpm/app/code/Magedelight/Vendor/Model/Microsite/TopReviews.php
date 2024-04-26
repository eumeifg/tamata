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
namespace Magedelight\Vendor\Model\Microsite;

use Magedelight\Vendor\Api\Data\MicrositeTopReviewsInterface;

class TopReviews extends \Magento\Framework\DataObject implements MicrositeTopReviewsInterface
{

    /**
     * {@inheritdoc}
     */
    public function getAvgRating()
    {
        return $this->getData('avg_rating');
    }

    /**
     * {@inheritdoc}
     */
    public function setAvgRating($rating)
    {
        return $this->setData('avg_rating', $rating);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData('comment');
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData('comment', $comment);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerName()
    {
        return $this->getData('customer_name');
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerName($name)
    {
        return $this->setData('customer_name', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($date)
    {
        return $this->setData('created_at', $date);
    }
}
