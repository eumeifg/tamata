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

namespace Magedelight\Review\Api\Data;

//use Magento\Tests\NamingConvention\true\string;

/**
 * @api
 */
interface ReviewDataInterface
{

    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const PRODUCT_ID = 'product_id';
    const NICKNAME = 'nickname';
    const TITLE = 'title';
    const REVIEW_DETAIL = 'review_detail';
    const RATING_DATA = 'rating_data';
    const STORE_ID = 'store_id';

    /**
     * @param string $productName.
     * @return $this
     */
    public function setProductName(string $productName);

   /**
    * @return string $productName.
    */
    public function getProductName();

   /**
    * @param string $ratingAvg
    * @return $this
    */
    public function setRatingAvg(string $ratingAvg);

   /**
    * @return string $ratingAvg
    */
    public function getRatingAvg();

   /**
    * @param string $reviewDate
    * @return $this
    */
    public function setReviewDate(string $reviewDate);

   /**
    * @return string $reviewDate
    */
    public function getReviewDate();

   /**
    * @param string $reviewDate
    * @return $this
    */
    public function setReviewDetail(string $reviewDetail);

   /**
    * @return string $reviewDate
    */
    public function getReviewDetail();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return int $productId
     */
    public function getProductId();

    /**
     * @param string $imagePath
     * @return $this
     */
    public function setProductImage($imagePath);

    /**
     * @return string $imagePath
     */
    public function getProductImage();

    /**
     * @param string $nickname
     * @return $this
     */
    public function setNickname(string $nickname);

    /**
     * @return string $nickname
     */
    public function getNickname();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * @return string $title
     */
    public function getTitle();

    /**
     * @param \Magedelight\Review\Api\Data\RatingDataInterface[] $ratingData
     * @return $this
     */
    public function setRatingData($ratingData);

    /**
     * @return \Magedelight\Review\Api\Data\RatingDataInterface[] $ratingData
     */
    public function getRatingData();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return int $storeId
     */
    public function getStoreId();
}
