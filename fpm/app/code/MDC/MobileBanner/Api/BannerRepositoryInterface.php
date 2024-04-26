<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MDC\MobileBanner\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BannerRepositoryInterface
{

    /**
     * Save banner
     * @param \MDC\MobileBanner\Api\Data\BannerInterface $banner
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MDC\MobileBanner\Api\Data\BannerInterface $banner
    );

    /**
     * Retrieve banner
     * @param string $entityId
     * @return \MDC\MobileBanner\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve banner matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MDC\MobileBanner\Api\Data\BannerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete banner
     * @param \MDC\MobileBanner\Api\Data\BannerInterface $banner
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \MDC\MobileBanner\Api\Data\BannerInterface $banner
    );

    /**
     * Delete banner by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}

