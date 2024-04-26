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
namespace Ktpl\PromoCallouts\Api\Data;

/**
 * Vendor interface.
 */
interface VendorInterface
{
    const VENDOR_ID = 'vendor_id';
    const NAME = 'name';
    const LOGO = 'logo';
    const BUSINESS_NAME = 'business_name';

    /**
     * Get vendor id
     *
     * @api
     * @return int
     */
    public function getId();

    /**
     * Set vendor id
     *
     * @api
     * @param int $vendorId
     * @return $this
     */
    public function setId($vendorId);

    /**
     * Get first name
     *
     * @api
     * @return string
     */
    public function getName();

    /**
     * Set first name
     *
     * @api
     * @param string|null $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get BusinessName
     *
     * @api
     * @return string
     */
    public function getBusinessName();
    
    /**
     * Set BusinessName
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setBusinessName($var);
    
    /**
     * Get Logo
     *
     * @api
     * @return string
     */
    public function getLogo();
    
    /**
     * Set Logo
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setLogo($var);
}
