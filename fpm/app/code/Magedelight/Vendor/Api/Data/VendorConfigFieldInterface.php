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
 * Vendor Config Fields interface.
 * @api
 */
interface VendorConfigFieldInterface
{
    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const BUSINESS_FIELDS = 'business_fields';
    const PERSONAL_FIELDS = 'personal_fields';
    const SHIPPING_FIELDS = 'shipping_fields';
    
    /**
     * Get Business Fields
     * @return \Magedelight\Vendor\Api\Data\ConfigFieldInterface[]
     */
    public function getBusinessConfigFields();

    /**
     * Set Business Fields
     * @param \Magedelight\Vendor\Api\Data\ConfigFieldInterface[] $businessData
     * @return $this
     */
    public function setBusinessConfigFields($businessData);

    /**
     * Get Personal Fields
     * @return \Magedelight\Vendor\Api\Data\ConfigFieldInterface[]
     */
    public function getPersonalConfigFields();

    /**
     * Set Personal Fields
     * @param \Magedelight\Vendor\Api\Data\ConfigFieldInterface[] $personalData
     * @return $this
     */
    public function setPersonalConfigFields($personalData);
    
    /**
     * Get Shipping Fields
     *
     * @api
     * @return \Magedelight\Vendor\Api\Data\ConfigFieldInterface[]
     */
    public function getShippingConfigFields();

    /**
     * Set Shipping Fields
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\ConfigFieldInterface[] $shippingData
     * @return $this
     */
    public function setShippingConfigFields($shippingData);
}
