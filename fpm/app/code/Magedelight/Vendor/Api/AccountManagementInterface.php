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
 * Interface for managing vendors accounts.
 */
interface AccountManagementInterface
{
   /**#@+
     * Constant for confirmation status
     */
    const ACCOUNT_CONFIRMED = 'vendor_account_confirmed';
    const ACCOUNT_CONFIRMATION_REQUIRED = 'vendor_account_confirmation_required';
    const ACCOUNT_CONFIRMATION_NOT_REQUIRED = 'vendor_account_confirmation_not_required';
    
    /**
     * These are used for webapi Table - oauth_token
     */
    const PERMISSION_SELLER = 'seller';
    const USER_TYPE_SELLER = 5;
    /**#@-*/

    /**
     * Create vendor account. Perform necessary business operations like sending email.
     *
     * @api
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @param array $postData
     * @param string|null $password
     * @param string $redirectUrl
     * @param string $createdBy
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAccount(
        \Magedelight\Vendor\Api\Data\VendorInterface $vendor,
        $postData = [],
        $password = null,
        $redirectUrl = '',
        $createdBy = 'vendor'
    );

    /**
     * Authenticate a vendor by username and password
     *
     * @api
     * @param string $email
     * @param string $password
     * @return \Magedelight\Vendor\Api\Data\VendorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authenticate($email, $password);
    
    /**
     * Change vendor password.
     *
     * @param int $vendorId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePasswordById($vendorId, $currentPassword, $newPassword);
    
    /**
     * Change vendor password.
     *
     * @api
     * @param string $email
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function changePassword($email, $currentPassword, $newPassword);

    /**
     * Gets the account confirmation status.
     *
     * @api
     * @param int $vendorId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfirmationStatus($vendorId);
 
    /**
     * Return hashed password, which can be directly saved to database.
     *
     * @api
     * @param string $password
     * @return string
     */
    public function getPasswordHash($password);
    
    /**
     * Send an email to the vendor with a password reset link.
     *
     * @api
     * @param string $email
     * @param string $template
     * @param int $websiteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initiatePasswordReset($email, $template, $websiteId = null);
    
    /**
     * Reset vendor password.
     *
     * @api
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetPassword($email, $resetToken, $newPassword);

    /**
     * Check if password reset token is valid.
     *
     * @api
     * @param int $vendorId
     * @param string $resetPasswordLinkToken
     * @return bool True if the token is valid
     * @throws \Magento\Framework\Exception\State\InputMismatchException If token is mismatched
     * @throws \Magento\Framework\Exception\State\ExpiredException If token is expired
     * @throws \Magento\Framework\Exception\InputException If token or vendor id is invalid
     * @throws \Magento\Framework\Exception\NoSuchEntityException If vendor doesn't exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateResetPasswordLinkToken($vendorId, $resetPasswordLinkToken);
    
    /**
     * send vendor status change request to admin with email notification
     * @param \Magedelight\Vendor\Api\VendorInterface $vendor
     * @param int|null $statusRequestType
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendVendorStatusRequest(\Magedelight\Vendor\Api\Data\VendorInterface $vendor, $statusRequestType);
    
    /**
     * @api
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    public function sendVerificationMail();
    
    /**
     * @api
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    public function registerVendor();
    
    /**
     * @api
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    public function registerPostVendor();

    /**
     * @api
     * @param string $vendorEmail
     * @param int  $websiteId
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    public function isEmailAvailable($vendorEmail, $websiteId);

    /**
     * @api
     * @param \Magedelight\Vendor\Api\Data\VendorInterface $vendor
     * @param mixed $data
     * @return \Magedelight\Sales\Api\Data\CustomMessageInterface
     */
    public function submitVendorQuery(\Magedelight\Vendor\Api\Data\VendorInterface $vendor, $data);
}
