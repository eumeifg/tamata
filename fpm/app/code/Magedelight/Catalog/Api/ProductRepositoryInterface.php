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

interface ProductRepositoryInterface
{
    /**
     * Get info about Vendor product
     *
     * @param int $id
     * @param string $field
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magedelight\Catalog\Api\Data\ProductVendorDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id, $field = null, $storeId = null, $forceReload = false);

    /**
     * Get info about Vendor product
     *
     * @param int $id
     * @param string $field
     * @param bool $editMode
     * @param int|null $storeId
     * @param bool $forceReload
     * @return \Magedelight\Catalog\Api\Data\ProductVendorDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    /*public function getMobileProductData($entity,$attributes);*/
}
