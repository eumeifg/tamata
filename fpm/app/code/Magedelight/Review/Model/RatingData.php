<?php

namespace Magedelight\Review\Model;

use Magedelight\Review\Api\Data\RatingDataInterface;

class RatingData extends \Magento\Framework\DataObject implements RatingDataInterface
{

   /**
    * {@inheritdoc}
    */
    public function setRatingId($ratingId)
    {
        return $this->setData(RatingDataInterface::RATING_ID, $ratingId);
    }

   /**
    * {@inheritdoc}
    */
    public function getRatingId()
    {
        return $this->getData(RatingDataInterface::RATING_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRatingCode($ratingCode)
    {
        return $this->setData(RatingDataInterface::RATING_CODE, $ratingCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingCode()
    {
        return $this->getData(RatingDataInterface::RATING_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRatingValue($ratingValue)
    {
        return $this->setData(RatingDataInterface::RATING_VALUE, $ratingValue);
    }

    /**
     * {@inheritdoc}
     */
    public function getRatingValue()
    {
        return $this->getData(RatingDataInterface::RATING_VALUE);
    }
}
