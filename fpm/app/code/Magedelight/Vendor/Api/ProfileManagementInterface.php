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

namespace Magedelight\Vendor\Api;

/**
 * @api
 * Interface for managing vendors profile.
 */
interface ProfileManagementInterface
{

    /**
     * @param \Magedelight\Vendor\Api\Data\StatusInterface $vendor
     * @param int $statusRequestType
     * @param bool $isAdmin
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorStatusChangeRequest(
        \Magedelight\Vendor\Api\Data\StatusInterface $vendor,
        $statusRequestType,
        $isAdmin = false
    );

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\PersonalDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorPersonalInfoUpdate(
        \Magedelight\Vendor\Api\Data\PersonalDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    );

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\BusinessDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorBusinessInfoUpdate(
        \Magedelight\Vendor\Api\Data\BusinessDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    );

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\ShippingDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorShippingInfoUpdate(
        \Magedelight\Vendor\Api\Data\ShippingDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    );

    /**
     *
     * @param \Magedelight\Vendor\Api\Data\BankDataInterface $vendor
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorBankingInfoUpdate(
        \Magedelight\Vendor\Api\Data\BankDataInterface $vendor,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    );

    /**
     *
     * @param int|null $vendorId
     * @param bool $isAdmin
     * @param bool $returnFirstError
     * @param bool $isVendorEdit
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function vendorLogoUpdate(
        $vendorId = null,
        $isAdmin = false,
        $returnFirstError = false,
        $isVendorEdit = false
    );
}
