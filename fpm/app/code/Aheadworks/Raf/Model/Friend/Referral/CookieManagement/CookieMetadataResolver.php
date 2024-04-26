<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Model\Friend\Referral\CookieManagement;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;

/**
 * Class CookieMetadataResolver
 *
 * @package Aheadworks\Raf\Model\Friend\Referral\CookieManagement
 */
class CookieMetadataResolver
{
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var ConfigInterface
     */
    private $sessionConfig;

    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigInterface $sessionConfig
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Resolve metadata
     *
     * @param PublicCookieMetadata|null $metadata
     * @return PublicCookieMetadata
     */
    public function resolve($metadata)
    {
        return !empty($metadata) || ($metadata instanceof PublicCookieMetadata) ? $metadata : $this->getMetadata();
    }

    /**
     * Retrieve cookie metadata
     *
     * @return PublicCookieMetadata
     */
    private function getMetadata()
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata();

        $cookieMetadata
            ->setDomain($this->sessionConfig->getCookieDomain())
            ->setPath($this->sessionConfig->getCookiePath())
            ->setDurationOneYear();

        return $cookieMetadata;
    }
}
