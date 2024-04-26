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

interface ProductStoreRepositoryInterface
{
    /**
     * Save Product Store.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore);

    /**
     * Retrieve Product Store
     *
     * @param int $ProductStoreId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ProductStoreId);
    
    /**
     * Retrieve Product Store By Product Id
     *
     * @param int $productId
     * @param type int | null $storeId
     * @return \Magedelight\Catalog\Api\Data\ProductStoreInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductStoreData($productId, $storeId = null);

    /**
     * Retrieve Product Stores matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Catalog\Api\Data\ProductStoreSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Product Store.
     *
     * @param \Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Catalog\Api\Data\ProductStoreInterface $ProductStore);

    /**
     * Delete Product Store by ID.
     *
     * @param int $ProductStoreId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ProductStoreId);
}
