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

use Magedelight\Vendor\Api\Data\BankDataInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class BankData extends AbstractExtensibleModel implements BankDataInterface
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
    * Get bank account name
    * @return string
    */
    public function getBankAccountName()
    {
        return $this->getData(self::BANK_ACCOUNT_NAME);
    }
    
    /**
     * Set bank account name
     * @param string|null $var
     * @return $this
     */
    public function setBankAccountName($var)
    {
        return $this->setData(self::BANK_ACCOUNT_NAME, $var);
    }

    /**
     * Get bank account number
     * @return int
     */
    public function getBankAccountNumber()
    {
        return $this->getData(self::BANK_ACCOUNT_NUMBER);
    }
    
    /**
     * Set bank account number
     * @param int|null $var
     * @return $this
     */
    public function setBankAccountNumber($var)
    {
        return $this->setData(self::BANK_ACCOUNT_NUMBER, $var);
    }
    
    /**
     * Get bank account number
     * @return int
     */
    public function getConfirmAccountNumber()
    {
        return $this->getData(self::CONFIRM_ACCOUNT_NUMBER);
    }
    
    /**
     * Set bank account number
     * @param int|null $var
     * @return $this
     */
    public function setConfirmAccountNumber($var)
    {
        return $this->setData(self::CONFIRM_ACCOUNT_NUMBER, $var);
    }

    /**
     * Get getBankName
     * @return string
     */
    public function getBankName()
    {
        return $this->getData(self::BANK_NAME);
    }
    
    /**
     * Set getBankName
     * @param string|null $var
     * @return $this
     */
    public function setBankName($var)
    {
        return $this->setData(self::BANK_NAME, $var);
    }
    
    /**
     * Get IFSC CODE
     * @return string
     */
    public function getIfsc()
    {
        return $this->getData(self::IFSC);
    }
    
    /**
     * Set IFSC CODE
     * @param string|null $var
     * @return $this
     */
    public function setIfsc($var)
    {
        return $this->setData(self::IFSC, $var);
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
     * @param \Magedelight\Vendor\Api\Data\BankDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magedelight\Vendor\Api\Data\BankDataExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
