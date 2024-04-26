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

use Magedelight\Vendor\Api\Data\StatusInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Status extends AbstractExtensibleModel implements StatusInterface
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
    public function getVacationFromDate()
    {
        return $this->getData(self::VACATION_FROM_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVacationFromDate($vacationFromDate)
    {
        return $this->setData(self::VACATION_FROM_DATE, $vacationFromDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getVacationToDate()
    {
        return $this->getData(self::VACATION_TO_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVacationToDate($vacationToDate)
    {
        return $this->setData(self::VACATION_TO_DATE, $vacationToDate);
    }

    /**
     * {@inheritDoc}
     */
    public function getVacationMessage()
    {
        return $this->getData(self::VACATION_MESSAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setVacationMessage($vacationMessage)
    {
        return $this->setData(self::VACATION_MESSAGE, $vacationMessage);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
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
     * @param \Magedelight\Vendor\Api\Data\StatusExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magedelight\Vendor\Api\Data\StatusExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
