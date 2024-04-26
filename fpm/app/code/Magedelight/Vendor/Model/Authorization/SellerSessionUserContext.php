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
namespace Magedelight\Vendor\Model\Authorization;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magedelight\Backend\Model\Auth\Session as SellerSession;

/**
 * Session-based customer user context
 */
class SellerSessionUserContext implements UserContextInterface
{
    /**
     * @var SellerSession
     */
    protected $_sellerSession;

    /**
     * Initialize dependencies.
     *
     * @param SellerSession $sellerSession
     */
    public function __construct(
        SellerSession $sellerSession
    ) {
        $this->_sellerSession = $sellerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->_sellerSession->getVendorId();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserType()
    {
        return AccountManagementInterface::USER_TYPE_SELLER;
    }
}
