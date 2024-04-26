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

interface VendorProductRepositoryInterface
{
    /**
     * Save Product.
     *
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterface $Product
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\VendorProductInterface $Product);

    /**
     * Retrieve Product
     *
     * @param int $ProductId
     * @return \Magedelight\Catalog\Api\Data\VendorProductInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ProductId);

    /**
     * Retrieve Products matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product.
     *
     * @param \Magedelight\Catalog\Api\Data\VendorProductInterface $Product
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\VendorProductInterface $Product);

    /**
     * Delete Product by ID.
     *
     * @param int $ProductId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ProductId);

    /**
     * Retrieve Vendor Products.
     *
     * @param string $type
     * @param int|null $storeId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param string|null $searchterm
     * @param boolean $outOfStockFilter
     * @return \Magedelight\Catalog\Api\Data\VendorProductSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getListingProducts(
        $type,
        $storeId,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        $searchterm = null,
        $outOfStockFilter = false
    );
}
