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

interface MicrositeTopReviewsInterface
{

    /**
     * Get vendor average rating
     *
     * @return float
     */
    public function getAvgRating();

    /**
     * Set vendor average rating
     *
     * @param float $rating
     * @return $this
     */
    public function setAvgRating($rating);

    /**
     * Get customer review description
     *
     * @return string
     */
    public function getComment();

    /**
     * Set customer review description
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment);

    /**
     * Get customer name
     *
     * @return string|null
     */
    public function getCustomerName();

    /**
     * Set customer name
     *
     * @param string $name
     * @return $this
     */
    public function setCustomerName($name);

    /**
     * Get review's created date.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set review's created date.
     *
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);
}
