<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\StoreSwitcher\Model;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;

class StoreCookieManager extends \Magento\Store\Model\StoreCookieManager
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->request = $request;
    }
    /**
     * Cookie name
     */
    const SELLER_COOKIE_NAME = 'seller-store';
    
    /**
     * {@inheritdoc}
     */
    public function getStoreCodeFromCookie()
    {
        return $this->cookieManager->getCookie($this->getCookieName());
    }
    
    /**
     * {@inheritdoc}
     */
    public function setStoreCookie(StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDurationOneYear()
            ->setPath($store->getStorePath());

        $this->cookieManager->setPublicCookie($this->getCookieName(), $store->getCode(), $cookieMetadata);
    }
    
    /**
     * {@inheritdoc}
     */
    public function deleteStoreCookie(StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($store->getStorePath());

        $this->cookieManager->deleteCookie($this->getCookieName(), $cookieMetadata);
    }
    
    protected function getCookieName()
    {
        if (strpos($this->request->getPathInfo(), 'seller') !== false && $this->request->getRequestUri() !== '/rbvendor/sellerdirectory') {
            return self::SELLER_COOKIE_NAME;
        } else {
            return self::COOKIE_NAME;
        }
    }
}
