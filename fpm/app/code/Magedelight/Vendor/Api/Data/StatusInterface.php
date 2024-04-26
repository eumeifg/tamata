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
 * Vendor interface.
 */
interface StatusInterface extends ExtensibleDataInterface
{
    /*     * #@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */

    const ID = 'vendor_id';
    const STORE_ID = 'store_id';
    const VACATION_FROM_DATE = 'vacation_from_date';
    const VACATION_TO_DATE = 'vacation_to_date';
    const VACATION_MESSAGE = 'vacation_message';

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
     * Get Store id
     * @return int
     */
    public function getStoreId();

    /**
     * Set Store id
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get Vacation from date
     * @return string
     */
    public function getVacationFromDate();

    /**
     * Set Vacation From Date
     * @param string $vacationFromDate
     * @return $this
     */
    public function setVacationFromDate($vacationFromDate);

    /**
     * Get Vacation To date
     * @return string
     */
    public function getVacationToDate();

    /**
     * Set Vacation To Date
     * @param string $vacationToDate
     * @return $this
     */
    public function setVacationToDate($vacationToDate);

    /**
     * Get Vacation Message
     * @return string
     */
    public function getVacationMessage();

    /**
     * Set Vacation Message
     * @param string $vacationMessage
     * @return $this
     */
    public function setVacationMessage($vacationMessage);
    
    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\StatusExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\StatusExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magedelight\Vendor\Api\Data\StatusExtensionInterface $extensionAttributes);
}
