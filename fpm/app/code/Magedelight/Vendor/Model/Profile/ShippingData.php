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

namespace Magedelight\Vendor\Model\Profile;

use Magedelight\Vendor\Api\Data\PersonalDataInterface;
use Magedelight\Vendor\Api\Data\ShippingDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ShippingData extends AbstractExtensibleModel implements ShippingDataInterface
{

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupAddress1()
    {
        if ($this->getData(self::PICKUP_ADDRESS1)) {
            return $this->getData(self::PICKUP_ADDRESS1);
        } else {
            return $this->getData(PersonalDataInterface::ADDRESS1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupAddress1($var)
    {
        return $this->setData(self::PICKUP_ADDRESS1, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupAddress2()
    {
        if ($this->getData(self::PICKUP_ADDRESS2)) {
            return $this->getData(self::PICKUP_ADDRESS2);
        } else {
            return $this->getData(PersonalDataInterface::ADDRESS2);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupAddress2($var)
    {
        return $this->setData(self::PICKUP_ADDRESS2, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupCity()
    {
        if ($this->getData(self::PICKUP_CITY)) {
            return $this->getData(self::PICKUP_CITY);
        } else {
            return $this->getData(PersonalDataInterface::CITY);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupCity($var)
    {
        return $this->setData(self::PICKUP_CITY, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupRegion()
    {
        if ($this->getData(self::PICKUP_REGION)) {
            return $this->getData(self::PICKUP_REGION);
        } else {
            return $this->getData(PersonalDataInterface::REGION);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupRegion($var)
    {
        return $this->setData(self::PICKUP_REGION, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupRegionId()
    {
        if ($this->getData(self::PICKUP_REGION_ID)) {
            return $this->getData(self::PICKUP_REGION_ID);
        } else {
            return $this->getData(PersonalDataInterface::REGION_ID);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupRegionId($var)
    {
        return $this->setData(self::PICKUP_REGION_ID, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupCountry()
    {
        if ($this->getData(self::PICKUP_COUNTRY)) {
            return $this->getData(self::PICKUP_COUNTRY);
        } else {
            return $this->getData(PersonalDataInterface::COUNTRY);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupCountry($var)
    {
        return $this->setData(self::PICKUP_COUNTRY, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPickupPincode()
    {
        if ($this->getData(self::PICKUP_PINCODE)) {
            return $this->getData(self::PICKUP_PINCODE);
        } else {
            return $this->getData(PersonalDataInterface::PINCODE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPickupPincode($var)
    {
        return $this->setData(self::PICKUP_PINCODE, $var);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magedelight\Vendor\Api\Data\ShippingDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\ShippingDataExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
