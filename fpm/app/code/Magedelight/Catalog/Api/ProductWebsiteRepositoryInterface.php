<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Api;

interface ProductWebsiteRepositoryInterface
{
    /**
     * Save Product Website.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite);

    /**
     * Retrieve Product Website
     *
     * @param int $ProductWebsiteId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ProductWebsiteId);
    
    /**
     * Retrieve Product Website
     *
     * @param int $productId
     * @param int | null $websiteId
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductWebsiteData($productId, $websiteId = null);

    /**
     * Retrieve Product Websites matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductWebsiteSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product Website.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductWebsiteInterface $ProductWebsite);

    /**
     * Delete Product Website by ID.
     *
     * @param int $ProductWebsiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ProductWebsiteId);
}
