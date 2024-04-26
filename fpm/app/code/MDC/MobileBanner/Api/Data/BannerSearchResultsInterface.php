<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Api\Data;

interface BannerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get banner list.
     * @return \MDC\MobileBanner\Api\Data\BannerInterface[]
     */
    public function getItems();

    /**
     * Set section_id list.
     * @param \MDC\MobileBanner\Api\Data\BannerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

