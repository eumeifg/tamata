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
namespace Ktpl\PromoCallouts\Model;

use Ktpl\PromoCallouts\Api\Data\VendorInterface;

class Vendor extends \Magento\Framework\DataObject implements VendorInterface
{

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    /**
     *
     * {@inheritDoc}
     */
    public function getBusinessName()
    {
        return $this->getData(self::BUSINESS_NAME);
    }

    /**
     *
     * {@inheritDoc}
     */
    public function getLogo()
    {
        return $this->getData(self::LOGO);
    }

    /**
     * {@inheritDoc}
     */
    public function setBusinessName($var)
    {
        return $this->setData(self::BUSINESS_NAME, $var);
    }

    /**
     *
     * {@inheritDoc}
     */
    public function setLogo($var)
    {
        return $this->setData(self::LOGO, $var);
    }
}
