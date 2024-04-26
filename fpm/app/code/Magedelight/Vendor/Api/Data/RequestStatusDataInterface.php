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
 * Vendor Request Status interface.
 * @api
 */
interface RequestStatusDataInterface extends ExtensibleDataInterface
{

    /**
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ID = 'request_id';
    const VENDOR_ID = 'vendor_id';
    const REQUEST_TYPE = 'request_type';
    const REQUEST_TYPE_TXT = 'request_type_text';
    const REASON = 'reason';
    const REQUESTED_AT = 'requested_at';
    const APPROVED_AT = 'approved_at';
    const STATUS = 'status';
    const STATUS_TEXT = 'status_text';
    const VACATION_FROM_DATE = 'vacation_from_date';
    const VACATION_TO_DATE = 'vacation_to_date';

    /**
     * Get Request Id
     * @return int
     */
    public function getRequestId();

    /**
     * Set Request id
     * @param int|null $id
     * @return $this
     */
    public function setRequestId($id);

    /**
     * Get Vendor Id
     * @return int
     */
    public function getVendorId();

    /**
     * Set Vendor Id
     * @param int $vendorId
     * @return $this
     */
    public function setVendorId($vendorId);

    /**
     * Get Request Type
     * @return int
     */
    public function getRequestType();

    /**
     * Set Request Type
     * @param int $reqType
     * @return $this
     */
    public function setRequestType($reqType);

    /**
     * Get Request Type Text
     * @return string|null
     */
    public function getRequestTypeText();

    /**
     * Set Request Type Text
     * @param string|null $reqTypeText
     * @return $this
     */
    public function setRequestTypeText($reqTypeText);

    /**
     * Get Reason
     * @return string
     */
    public function getReason();

    /**
     * Set Reason
     * @param string $var
     * @return $this
     */
    public function setReason($var);

    /**
     * Get Requested At
     * @return string
     */
    public function getRequestedAt();

    /**
     * Set Requested At
     * @param string $var
     * @return $this
     */
    public function setRequestedAt($var);

    /**
     * Get Approved At
     * @return string
     */
    public function getApprovedAt();

    /**
     * Set Approved At
     * @param string|null $var
     * @return $this
     */
    public function setApprovedAt($var);

    /**
     * Get Status
     * @return string
     */
    public function getStatus();

    /**
     * Set Status
     * @param string|null $var
     * @return $this
     */
    public function setStatus($var);

    /**
     * Get Status Text
     * @return string|null
     */
    public function getStatusText();

    /**
     * Set Status
     * @param string|null $var
     * @return $this
     */
    public function setStatusText($var);

    /**
     * Get Vacation From Date
     * @return string
     */
    public function getVacationFromDate();

    /**
     * Set Vacation From Date
     * @param string|null $var
     * @return $this
     */
    public function setVacationFromDate($var);

    /**
     * Get Vacation To Date
     * @return string
     */
    public function getVacationToDate();

    /**
     * Set Vacation To Date
     * @param string|null $var
     * @return $this
     */
    public function setVacationToDate($var);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magedelight\Vendor\Api\Data\RequestStatusDataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magedelight\Vendor\Api\Data\RequestStatusDataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magedelight\Vendor\Api\Data\RequestStatusDataExtensionInterface $extensionAttributes
    );
}
