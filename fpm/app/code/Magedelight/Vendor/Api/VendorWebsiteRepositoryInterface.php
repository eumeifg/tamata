<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Api;

/**
 * @api
 */
interface VendorWebsiteRepositoryInterface
{
    /**
     * Save Vendor Website.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite);

    /**
     * Retrieve Vendor Website
     *
     * @param int $vendorWebsiteId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($vendorWebsiteId);
    
    /**
     * Retrieve Vendor Website
     *
     * @param int $vendorId
     * @param int | null $websiteId
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVendorWebsiteData($vendorId, $websiteId = null);

    /**
     * Retrieve Vendor Websites matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Vendor\Api\Data\VendorWebsiteSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product Website.
     *
     * @param \Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Vendor\Api\Data\VendorWebsiteInterface $vendorWebsite);

    /**
     * Delete Vendor Website by ID.
     *
     * @param int $vendorWebsiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($vendorWebsiteId);
}
