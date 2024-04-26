<?php

namespace Magedelight\Review\Model;

use Magedelight\Review\Api\Data\ReviewDataInterface;

class ReviewData extends \Magento\Framework\DataObject implements ReviewDataInterface
{
   /**
    * {@inheritdoc}
    */
    public function setProductName(string $productName)
    {
        return $this->setData('product_name', $productName);
    }

   /**
    * {@inheritdoc}
    */
    public function getProductName()
    {
        return $this->getData('product_name');
    }

   /**
    * {@inheritdoc}
    */
    public function setRatingAvg(string $ratingAvg)
    {
        return $this->setData('rating_avg', $ratingAvg);
    }

   /**
    * {@inheritdoc}
    */
    public function getRatingAvg()
    {
        return $this->getData('rating_avg');
    }

   /**
    * {@inheritdoc}
    */
    public function setReviewDate(string $reviewDate)
    {
        return $this->setData('review_date', $reviewDate);
    }

   /**
    * {@inheritdoc}
    */
    public function getReviewDate()
    {
        return $this->getData('review_date');
    }

   /**
    * {@inheritdoc}
    */
    public function setReviewDetail(string $reviewDetail)
    {
        return $this->setData(ReviewDataInterface::REVIEW_DETAIL, $reviewDetail);
    }

   /**
    * {@inheritdoc}
    */
    public function getReviewDetail()
    {
        return $this->getData(ReviewDataInterface::REVIEW_DETAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(ReviewDataInterface::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(ReviewDataInterface::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductImage($imagePath)
    {
        return $this->setData('product_image', $imagePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductImage()
    {
        return $this->getData('product_image');
    }

    /**
     * {@inheritdoc}
     */
    public function setNickname(string $nickname)
    {
        return $this->setData(ReviewDataInterface::NICKNAME, $nickname);
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname()
    {
        return $this->getData(ReviewDataInterface::NICKNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle(string $title)
    {
        return $this->setData(ReviewDataInterface::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(ReviewDataInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRatingData($ratingData)
    {
        return $this->setData(ReviewDataInterface::RATING_DATA, $ratingData);
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingData()
    {
        return $this->getData(ReviewDataInterface::RATING_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(ReviewDataInterface::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(ReviewDataInterface::STORE_ID);
    }
}
