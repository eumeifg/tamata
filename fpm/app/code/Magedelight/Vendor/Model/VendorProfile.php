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
namespace Magedelight\Vendor\Model;

use Magedelight\Vendor\Api\Data\VendorProfileInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class VendorProfile extends AbstractExtensibleModel implements VendorProfileInterface
{

    /**
     * {@inheritDoc}
     */
    public function getPersonalInformation()
    {
        return $this->getData(self::PERSONAL_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setPersonalInformation($personalData)
    {
        return $this->setData(self::PERSONAL_INFO, $personalData);
    }

    /**
     * {@inheritDoc}
     */
    public function getBusinessInformation()
    {
        return $this->getData(self::BUSINESS_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setBusinessInformation($businessData)
    {
        return $this->setData(self::BUSINESS_INFO, $businessData);
    }

    /**
     * {@inheritDoc}
     */
    public function getLoginInformation()
    {
        return $this->getData(self::LOGIN_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setLoginInformation($loginData)
    {
        return $this->setData(self::LOGIN_INFO, $loginData);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryItems($categoryData)
    {
        return $this->setData('categories', $categoryData);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryItems()
    {
        return $this->getData('categories');
    }

    /**
     * {@inheritdoc}
     */
    public function addCategoryItem($categoryData)
    {
        $this->setCategoryItems(array_filter(array_merge([$this->getCategoryItems()], $categoryData)));
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusInformation()
    {
        return $this->getData(self::STATUS_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatusInformation($statusData)
    {
        return $this->setData(self::STATUS_INFO, $statusData);
    }

    /**
     * {@inheritDoc}
     */
    public function getShippingInformation()
    {
        return $this->getData(self::SHIPPING_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setShippingInformation($shippingData)
    {
        return $this->setData(self::SHIPPING_INFO, $shippingData);
    }

    /**
     * {@inheritDoc}
     */
    public function getBankingInformation()
    {
        return $this->getData(self::BANKING_INFO);
    }

    /**
     * {@inheritDoc}
     */
    public function setBankingInformation($bankingData)
    {
        return $this->setData(self::BANKING_INFO, $bankingData);
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
     * @param \Magedelight\Vendor\Api\Data\VendorProfileExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\VendorProfileExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
