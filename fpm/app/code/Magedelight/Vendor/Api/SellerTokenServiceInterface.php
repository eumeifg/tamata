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
 * Interface providing token generation for Seller
 *
 * @api
 * @since 100.0.2
 */
interface SellerTokenServiceInterface
{
    /**
     * Create access token for admin given the seller credentials.
     *
     * @param string $username
     * @param string $password
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function createSellerAccessToken($username, $password);

    /**
     * Revoke token by seller id.
     *
     * @param int $sellerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeSellerAccessToken($sellerId);
}
