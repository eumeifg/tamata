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
namespace Magedelight\Vendor\Api\Data;

/**
 * @api
 */
interface VendorWebsiteInterface
{
    /**
     * ID
     *
     * @var string
     */
    const VENDORWEBSITE_ID = 'vendor_website_id';

    /**
     * Vendor ID attribute constant
     *
     * @var string
     */
    const VENDOR_ID = 'vendor_id';
    
    /**
     * Status attribute constant
     *
     * @var string
     */
    const STATUS = 'status';

    /**
     * Website ID attribute constant
     *
     * @var string
     */
    const WEBSITE_ID = 'website_id';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVendorWebsiteId();

    /**
     * Set ID
     *
     * @param int $vendorWebsiteId
     * @return VendorWebsiteInterface
     */
    public function setVendorWebsiteId($vendorWebsiteId);

    /**
     * Get Vendor ID
     *
     * @return mixed
     */
    public function getVendorId();

    /**
     * Set Vendor ID
     *
     * @param mixed $vendorId
     * @return VendorWebsiteInterface
     */
    public function setVendorId($vendorId);
    
    /**
     * Get Website ID
     *
     * @return mixed
     */
    public function getWebsiteId();

    /**
     * Set Website ID
     *
     * @param mixed $websiteId
     * @return VendorWebsiteInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get Status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param mixed $status
     * @return VendorWebsiteInterface
     */
    public function setStatus($status);
}
