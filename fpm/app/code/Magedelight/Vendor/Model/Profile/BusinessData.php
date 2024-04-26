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

use Magedelight\Vendor\Api\Data\BusinessDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class BusinessData extends AbstractExtensibleModel implements BusinessDataInterface
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
    public function getBusinessName()
    {
        return $this->getData(self::BUSINESS_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setBusinessName($var)
    {
        return $this->setData(self::BUSINESS_NAME, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function getVat()
    {
        return $this->getData(self::VAT);
    }

    /**
     * {@inheritDoc}
     */
    public function setVat($vat)
    {
        return $this->setData(self::VAT, $vat);
    }

    /**
     * {@inheritDoc}
     */
    public function getVatDoc()
    {
        return $this->getData(self::VAT_DOC);
    }

    /**
     * {@inheritDoc}
     */
    public function setVatDoc($vat_doc)
    {
        return $this->setData(self::VAT_DOC, $vat_doc);
    }

    /**
     * {@inheritDoc}
     */
    public function getOtherMarketplaceProfile()
    {
        return $this->getData(self::OTHER_MARKETPLACE_PROFILE);
    }

    /**
     * {@inheritDoc}
     */
    public function setOtherMarketplaceProfile($var)
    {
        return $this->setData(self::OTHER_MARKETPLACE_PROFILE, $var);
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
     * @param \Magedelight\Vendor\Api\Data\BusinessDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\BusinessDataExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
