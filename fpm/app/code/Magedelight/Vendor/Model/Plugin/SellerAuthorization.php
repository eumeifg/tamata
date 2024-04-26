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
namespace Magedelight\Vendor\Model\Plugin;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Plugin around \Magento\Framework\Authorization::isAllowed
 *
 * Plugin to allow customer users to access resources with self permission
 */
class SellerAuthorization
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Inject dependencies.
     *
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * Check if resource for which access is needed has self permissions defined in webapi config.
     *
     * @param \Magento\Framework\Authorization $subject
     * @param callable $proceed
     * @param string $resource
     * @param string $privilege
     *
     * @return bool true If resource permission is self, to allow
     * customer access without further checks in parent method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsAllowed(
        \Magento\Framework\Authorization $subject,
        \Closure $proceed,
        $resource,
        $privilege = null
    ) {
        if ($resource == AccountManagementInterface::PERMISSION_SELLER
            && $this->userContext->getUserId()
            && $this->userContext->getUserType() === AccountManagementInterface::USER_TYPE_SELLER
        ) {
            return true;
        } else {
            return $proceed($resource, $privilege);
        }
    }
}
