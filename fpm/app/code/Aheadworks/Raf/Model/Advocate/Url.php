<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Advocate;

use Magento\Framework\UrlInterface;

/**
 * Class Url
 *
 * @package Aheadworks\Raf\Model\Advocate
 */
class Url
{
    /**
     * @var string
     */
    const REFERRAL_PARAM = 'awraf';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve create referral link url
     *
     * @return string
     */
    public function getCreateReferralLinkUrl()
    {
        return $this->urlBuilder->getUrl('aw_raf/advocate/createLink');
    }

    /**
     * Retrieve referral url
     *
     * @param string $referralLink
     * @return string
     */
    public function getReferralUrl($referralLink)
    {
        $query = [self::REFERRAL_PARAM => $referralLink];
        $params = [
            '_query' => $query
        ];
        return $this->urlBuilder->getUrl(null, $params);
    }
}
