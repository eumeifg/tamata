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

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Vendor Business interface.
 * @api
 */
interface BusinessDataInterface extends ExtensibleDataInterface
{

    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'vendor_id';
    const BUSINESS_NAME = 'business_name';
    const VAT = 'vat';
    const VAT_DOC = 'vat_doc';
    const OTHER_MARKETPLACE_PROFILE ='other_marketplace_profile';

    /**
     * Get vendor id
     * @return int
     */
    public function getId();

    /**
     * Set vendor id
     * @param int|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get BusinessName
     *
     * @api
     * @return string
     */
    public function getBusinessName();

    /**
     * Set BusinessName
     * @param string|null $var
     * @return $this
     */
    public function setBusinessName($var);

    /**
     * @return mixed
     */
    public function getVat();

    /**
     * @param $vat
     * @return mixed|null
     */
    public function setVat($vat);

    /**
     * @return mixed
     */
    public function getVatDoc();

    /**
     * @param $vat_doc
     * @return mixed|null
     */
    public function setVatDoc($vat_doc);

    /**
     * Get product URL if sell in other Marketplace
     *
     * @api
     * @return string
     */
    public function getOtherMarketplaceProfile();

    /**
     * Set product URL if sell in other Marketplace
     *
     * @api
     * @param string|null $var
     * @return $this
     */
    public function setOtherMarketplaceProfile($var);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\BusinessDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\BusinessDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\BusinessDataExtensionInterface $extensionAttributes
    );
}
