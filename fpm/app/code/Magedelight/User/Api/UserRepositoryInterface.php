<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Api;

/**
 *
 * @author Rocket Bazaar Core Team
 */
interface UserRepositoryInterface
{
    /**
     * Create vendor.
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @param string $passwordHash
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magedelight\Vendor\Api\Data\VendorInterface $vendor, $passwordHash = null);

    /**
     * Retrieve vendor.
     *
     * @api
     * @param string $email
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified email does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($email);

    /**
     * Retrieve vendor.
     *
     * @api
     * @param int $userId
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($userId);
    
    /**
     * Retrieve vendor.
     *
     * @api
     * @param string $token
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByEmailVerificationCode($token);

    /**
     * Retrieve vendors which match a specified criteria.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magedelight\Vendor\Api\Data\VendorSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete vendor.
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Magedelight\Vendor\Api\Data\VendorInterface $vendor);

    /**
     * Delete vendor by ID.
     *
     * @api
     * @param int $vendorId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($vendorId);
}
