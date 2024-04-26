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
namespace Magedelight\Vendor\Controller\Rest;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;

/**
 * Replaces a "%seller_id%" value with the real customer id
 */
class ParamOverriderSellerId implements ParamOverriderInterface
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * Constructs an object to override the seller ID parameter on a request.
     *
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * {@inheritDoc}
     */
    public function getOverriddenValue()
    {
        if ($this->userContext->getUserType() === AccountManagementInterface::USER_TYPE_SELLER) {
            return $this->userContext->getUserId();
        }
        return null;
    }
}
