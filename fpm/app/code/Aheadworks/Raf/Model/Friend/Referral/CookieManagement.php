<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Friend\Referral;

use Aheadworks\Raf\Model\Friend\Referral\CookieManagement\CookieMetadataResolver;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class CookieManagement
 *
 * @package Aheadworks\Raf\Model\Friend\Referral
 */
class CookieManagement
{
    /**
     * @var string
     */
    const REFERRAL_COOKIE_NAME = 'aw-raf-referral';

    /**
     * @var string
     */
    const WELCOME_POPUP_COOKIE_NAME = 'aw-raf-welcome-popup';

    /**#@+
     * Welcome popup constants
     */
    const WELCOME_POPUP_COOKIE_VALUE_IS_SHOW = 'is_show';
    const WELCOME_POPUP_COOKIE_VALUE_DO_NOT_SHOW = 'do_not_show';
    const WELCOME_POPUP_COOKIE_VALUE_SHOW = 'show';
    /**#@-*/

    /**
     * @var CookieMetadataResolver
     */
    private $cookieMetadataResolver;

    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @param CookieMetadataResolver $cookieMetadataResolver
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        CookieMetadataResolver $cookieMetadataResolver,
        CookieManagerInterface $cookieManager
    ) {
        $this->cookieMetadataResolver = $cookieMetadataResolver;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Retrieve referral cookie value
     *
     * @return string
     */
    public function getReferralValue()
    {
        return $this->cookieManager->getCookie(self::REFERRAL_COOKIE_NAME);
    }

    /**
     * Set referral cookie value
     *
     * @param string $value
     * @param PublicCookieMetadata|null $metadata
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setReferralValue($value, $metadata = null)
    {
        $this->cookieManager->setPublicCookie(
            self::REFERRAL_COOKIE_NAME,
            $value,
            $this->cookieMetadataResolver->resolve($metadata)
        );
    }

    /**
     * Retrieve welcome popup cookie value
     *
     * @return string
     */
    public function getWelcomePopupValue()
    {
        return $this->cookieManager->getCookie(self::WELCOME_POPUP_COOKIE_NAME);
    }

    /**
     * Set welcome popup cookie value
     *
     * @param string $value
     * @param PublicCookieMetadata|null $metadata
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setWelcomePopupValue($value, $metadata = null)
    {
        $this->cookieManager->setPublicCookie(
            self::WELCOME_POPUP_COOKIE_NAME,
            $value,
            $this->cookieMetadataResolver->resolve($metadata)
        );
    }
}
