<?php

namespace CAT\OfferPage\Api\Data;

interface CustomMessageInterface
{
    /*
     * Message.
     */
    const MESSAGE = 'message';
    /*
     * Status.
     */
    const STATUS = 'status';

    /**
     * OFFERPAGE_ID
     */
    const OFFERPAGE_ID = 'offerpage_id';

    /**
     * OFFER_TITLE
     */
    const OFFER_TITLE = 'title';

    /**
     * BANNER_DETAILS
     */
    const BANNER_DETAILS = 'banner_details';

    /**
     * Get Message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set Message
     * @param string $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * Get Status
     *
     * @return bool|string|int
     */
    public function getStatus();

    /**
     * Set Status
     * @param bool|string|int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get OfferPageId
     *
     * @return int
     */
    public function getOfferpageId();

    /**
     * Set OfferPageId
     * @param int $offerPageId
     * @return $this
     */
    public function setOfferpageId($offerPageId);

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set Title
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get BannerDetails
     *
     * @return \CAT\OfferPage\Api\Data\BannerDetailsInterface[] Array of banners.
     */
    public function getBannerDetails();

    /**
     * Set BannerDetails
     * @param array $bannerDetails
     * @return $this
     */
    public function setBannerDetails($bannerDetails);
}
