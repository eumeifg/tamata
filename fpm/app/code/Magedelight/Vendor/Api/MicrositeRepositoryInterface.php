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
 * Microsite CRUD interface.
 */
interface MicrositeRepositoryInterface
{
    /**
     * Create microsite.
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\MicrositeInterface $microsite
     * @return \Magedelight\Vendor\Api\Data\MicrositeInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Vendor\Api\Data\MicrositeInterface $microsite);

    /**
     * Retrieve microsite.
     *
     * @api
     * @param int $micrositeId
     * @return \Magedelight\Vendor\Api\Data\MicrositeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($micrositeId);
    
    /**
     * Delete vendor.
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\MicrositeInterface $microsite
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Vendor\Api\Data\MicrositeInterface $microsite);

    /**
     * Delete microsite by ID.
     *
     * @api
     * @param int $micrositeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($micrositeId);

    /**
     * @param int $vendorId
     * @param int $storeId
     * @param array $columns
     * @param bool $forceReload
     * @return \Magedelight\Vendor\Api\Data\MicrositeInterface
     */
    public function getByVendorId($vendorId, $storeId, $columns = ['*'], $forceReload = false);
}
