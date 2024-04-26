<?php

namespace CAT\OfferPage\Model\WebApi;

use CAT\OfferPage\Api\Data\CustomMessageInterface;

class CustomMessage extends \Magento\Framework\DataObject implements CustomMessageInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData(CustomMessageInterface::MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->setData(CustomMessageInterface::MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(CustomMessageInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(CustomMessageInterface::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getOfferPageId()
    {
        return $this->getData(CustomMessageInterface::OFFERPAGE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOfferPageId($offerPageId)
    {
        $this->setData(CustomMessageInterface::OFFERPAGE_ID, $offerPageId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(CustomMessageInterface::OFFER_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(CustomMessageInterface::OFFER_TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function getBannerDetails()
    {
        return $this->getData(CustomMessageInterface::BANNER_DETAILS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBannerDetails($bannerDetails)
    {
        $this->setData(CustomMessageInterface::BANNER_DETAILS, $bannerDetails);
    }
}
