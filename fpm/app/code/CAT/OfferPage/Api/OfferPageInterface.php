<?php

namespace CAT\OfferPage\Api;

use CAT\OfferPage\Api\Data\CustomMessageInterface;

interface OfferPageInterface
{
    /**
     * @api
     * @param int $offerId
     * @return CustomMessageInterface
     */
    public function getOfferPage(int $offerId): CustomMessageInterface;
}
