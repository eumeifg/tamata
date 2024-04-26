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
use Magento\Framework\Model\AbstractExtensibleModel;

class PersonalData extends AbstractExtensibleModel implements PersonalDataInterface
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
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddress1()
    {
        return $this->getData(self::ADDRESS1);
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress1($var)
    {
        return $this->setData(self::ADDRESS1, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getAddress2()
    {
        return $this->getData(self::ADDRESS2);
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress2($var)
    {
        return $this->setData(self::ADDRESS2, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * {@inheritDoc}
     */
    public function setCountryId($var)
    {
        return $this->setData(self::COUNTRY, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * {@inheritDoc}
     */
    public function setRegion($var)
    {
        return $this->setData(self::REGION, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setRegionId($var)
    {
        return $this->setData(self::REGION_ID, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * {@inheritDoc}
     */
    public function setCity($var)
    {
        return $this->setData(self::CITY, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getPincode()
    {
        return $this->getData(self::PINCODE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPincode($var)
    {
        return $this->setData(self::PINCODE, $var);
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
     * @param \Magedelight\Vendor\Api\Data\PersonalDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\PersonalDataExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
