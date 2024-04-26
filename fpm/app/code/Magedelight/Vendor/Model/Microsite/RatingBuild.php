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

use Magedelight\Vendor\Api\Data\MicrositeRatingDataInterface;

/**
 * Rating Build getter-setter.
 */
class RatingBuild extends \Magento\Framework\DataObject implements MicrositeRatingDataInterface
{

    /**
     * {@inheritdoc}
     */
    public function getPositiveReviewsCount()
    {
        return $this->getData('positive_reviews_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setPositiveReviewsCount($count)
    {
        return $this->setData('positive_reviews_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getNeutralReviewsCount()
    {
        return $this->getData('neutral_reviews_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setNeutralReviewsCount($count)
    {
        return $this->setData('neutral_reviews_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getNegativeReviewsCount()
    {
        return $this->getData('negative_reviews_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setNegativeReviewsCount($count)
    {
        return $this->setData('negative_reviews_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getPositiveRatio()
    {
        return $this->getData('positive_ratio');
    }

    /**
     * {@inheritdoc}
     */
    public function setPositiveRatio($ratio)
    {
        return $this->setData('positive_ratio', $ratio);
    }

    /**
     * {@inheritdoc}
     */
    public function getOneStarCount()
    {
        return $this->getData('one_star_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setOneStarCount($count)
    {
        return $this->setData('one_star_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getTwoStarCount()
    {
        return $this->getData('two_star_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setTwoStarCount($count)
    {
        return $this->setData('two_star_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getThreeStarCount()
    {
        return $this->getData('three_star_count');
    }
    /**
     * {@inheritdoc}
     */
    public function setThreeStarCount($count)
    {
        return $this->setData('three_star_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getFourStarCount()
    {
        return $this->getData('four_star_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setFourStarCount($count)
    {
        return $this->setData('four_star_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getFiveStarCount()
    {
        return $this->getData('five_star_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setFiveStarCount($count)
    {
        return $this->setData('five_star_count', $count);
    }

    /**
     * {@inheritdoc}
     */
    public function getTopReviews()
    {
        return $this->getData('top_reviews');
    }

    /**
     * {@inheritdoc}
     */
    public function setTopReviews($reviews)
    {
        return $this->setData('top_reviews', $reviews);
    }
}
