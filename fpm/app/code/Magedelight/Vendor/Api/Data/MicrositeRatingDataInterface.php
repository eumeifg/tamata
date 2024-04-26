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

interface MicrositeRatingDataInterface
{

    /**
     * Get positive reviews count
     *
     * @return integer
     */
    public function getPositiveReviewsCount();

    /**
     * Set positive reviews count
     *
     * @param integer $count
     * @return $this
     */
    public function setPositiveReviewsCount($count);

    /**
     * Get neutral reviews count
     *
     * @return integer
     */
    public function getNeutralReviewsCount();

    /**
     * Set neutral reviews count
     *
     * @param integer $count
     * @return $this
     */
    public function setNeutralReviewsCount($count);

    /**
     * Get negative reviews count
     *
     * @return integer
     */
    public function getNegativeReviewsCount();

    /**
     * Set negative reviews count
     *
     * @param integer $count
     * @return $this
     */
    public function setNegativeReviewsCount($count);

    /**
     * Get positive ratio
     *
     * @return integer
     */
    public function getPositiveRatio();

    /**
     * Set positive ratio
     *
     * @param integer $ratio
     * @return $this
     */
    public function setPositiveRatio($ratio);

    /**
     * Get reviews count with one star
     *
     * @return integer
     */
    public function getOneStarCount();

    /**
     * Set reviews count with one star
     *
     * @param integer $count
     * @return $this
     */
    public function setOneStarCount($count);

    /**
     * Get reviews count with two star
     *
     * @return integer
     */
    public function getTwoStarCount();

    /**
     * Set reviews count with two star
     *
     * @param integer $count
     * @return $this
     */
    public function setTwoStarCount($count);

    /**
     * Get reviews count with three star
     *
     * @return integer
     */
    public function getThreeStarCount();

    /**
     * Set reviews count with three star
     *
     * @param integer $count
     * @return $this
     */
    public function setThreeStarCount($count);

    /**
     * Get reviews count with four star
     *
     * @return integer
     */
    public function getFourStarCount();

    /**
     * Set reviews count with four star
     *
     * @param integer $count
     * @return $this
     */
    public function setFourStarCount($count);

    /**
     * Get reviews count with five star
     *
     * @return integer
     */
    public function getFiveStarCount();

    /**
     * Set reviews count with five star
     *
     * @param integer $count
     * @return $this
     */
    public function setFiveStarCount($count);

    /**
     * Get vendor top reviews
     *
     * @return Magedelight\Vendor\Api\Data\MicrositeTopReviewsInterface[]|[]
     */
    public function getTopReviews();

    /**
     * Set vendor top reviews
     *
     * @param Magedelight\Vendor\Api\Data\MicrositeTopReviewsInterface[] $reviews
     * @return $this
     */
    public function setTopReviews($reviews);
}
