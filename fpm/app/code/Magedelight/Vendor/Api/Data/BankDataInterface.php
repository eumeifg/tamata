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
interface BankDataInterface extends ExtensibleDataInterface
{

    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'vendor_id';
    const BANK_ACCOUNT_NAME = 'bank_account_name';
    const BANK_ACCOUNT_NUMBER = 'bank_account_number';
    const CONFIRM_ACCOUNT_NUMBER = 'confirm_account_number';
    const BANK_NAME = 'bank_name';
    const IFSC = 'ifsc';

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
     * Get bank account name
     * @return string
     */
    public function getBankAccountName();

    /**
     * Set bank account name
     * @param string|null $var
     * @return $this
     */
    public function setBankAccountName($var);

    /**
     * Get bank account number
     * @return int
     */
    public function getBankAccountNumber();

    /**
     * Set bank account number
     * @param int|null $var
     * @return $this
     */
    public function setBankAccountNumber($var);

    /**
     * Get bank account number
     * @return int
     */
    public function getConfirmAccountNumber();

    /**
     * Set bank account number
     * @param int|null $var
     * @return $this
     */
    public function setConfirmAccountNumber($var);

    /**
     * Get getBankName
     * @return string
     */
    public function getBankName();

    /**
     * Set getBankName
     * @param string|null $var
     * @return $this
     */
    public function setBankName($var);

    /**
     * Get IFSC CODE
     * @return string
     */
    public function getIfsc();

    /**
     * Set IFSC CODE
     * @param string|null $var
     * @return $this
     */
    public function setIfsc($var);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\BankDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\BankDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\BankDataExtensionInterface $extensionAttributes
    );
}
