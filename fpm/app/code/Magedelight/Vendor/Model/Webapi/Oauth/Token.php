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
namespace Magedelight\Vendor\Model\Webapi\Oauth;

use Magedelight\Vendor\Api\AccountManagementInterface;

/**
 * oAuth token model
 *
 * @method string getName() Consumer name (joined from consumer table)
 * @method int getConsumerId()
 * @method Token setConsumerId() setConsumerId(int $consumerId)
 * @method int getAdminId()
 * @method Token setAdminId() setAdminId(int $adminId)
 * @method int getCustomerId()
 * @method Token setCustomerId() setCustomerId(int $customerId)
 * @method int getSellerId()
 * @method Token setSellerId() setSellerId(int $customerId)
 * @method int getUserType()
 * @method Token setUserType() setUserType(int $userType)
 * @method string getType()
 * @method Token setType() setType(string $type)
 * @method string getCallbackUrl()
 * @method Token setCallbackUrl() setCallbackUrl(string $callbackUrl)
 * @method string getCreatedAt()
 * @method Token setCreatedAt() setCreatedAt(string $createdAt)
 * @method string getToken()
 * @method Token setToken() setToken(string $token)
 * @method string getSecret()
 * @method Token setSecret() setSecret(string $tokenSecret)
 * @method int getRevoked()
 * @method Token setRevoked() setRevoked(int $revoked)
 * @method int getAuthorized()
 * @method Token setAuthorized() setAuthorized(int $authorized)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Token extends \Magento\Integration\Model\Oauth\Token
{
    /**
     * Create access token for a seller
     *
     * @param int $sellerId
     * @return $this
     */
    public function createSellerToken($sellerId)
    {
        $this->setSellerId($sellerId);
        return $this->saveAccessToken(AccountManagementInterface::USER_TYPE_SELLER, $sellerId);
    }

    /**
     * Get token by seller id
     *
     * @param int $sellerId
     * @return $this
     */
    public function loadBySellerId($sellerId)
    {
        $connection = $this->getResource()->getConnection();
        $select = $connection->select()
            ->from($this->getResource()->getMainTable())
            ->where('seller_id = ?', $sellerId)
            ->where('user_type = ?', AccountManagementInterface::USER_TYPE_SELLER);
        $tokenData = $connection->fetchRow($select);
        $this->setData($tokenData ? $tokenData : []);
        return $this;
    }
}
