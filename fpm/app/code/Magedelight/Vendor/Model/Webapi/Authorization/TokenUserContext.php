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
namespace Magedelight\Vendor\Model\Webapi\Authorization;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Oauth\Token;

/**
 * A user context determined by tokens in a HTTP request Authorization header.
 */
class TokenUserContext extends \Magento\Webapi\Model\Authorization\TokenUserContext
{
    /**
     * @param Token $token
     * @return void
     */
    protected function setUserDataViaToken(Token $token)
    {
        $this->userType = $token->getUserType();

        switch ($this->userType) {
            case UserContextInterface::USER_TYPE_INTEGRATION:
                $this->userId = $this->integrationService->findByConsumerId($token->getConsumerId())->getId();
                $this->userType = UserContextInterface::USER_TYPE_INTEGRATION;
                break;
            case UserContextInterface::USER_TYPE_ADMIN:
                $this->userId = $token->getAdminId();
                $this->userType = UserContextInterface::USER_TYPE_ADMIN;
                break;
            case UserContextInterface::USER_TYPE_CUSTOMER:
                $this->userId = $token->getCustomerId();
                $this->userType = UserContextInterface::USER_TYPE_CUSTOMER;
                break;
            case AccountManagementInterface::USER_TYPE_SELLER:
                $this->userId = $token->getSellerId();
                $this->userType = AccountManagementInterface::USER_TYPE_SELLER;
                break;
            default:
                /* this is an unknown user type so reset the cached user type */
                $this->userType = null;
        }
    }
}
