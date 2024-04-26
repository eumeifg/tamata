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
namespace Magedelight\Vendor\Cron;

use Magedelight\Vendor\Api\AccountManagementInterface;
use Magento\Integration\Model\ResourceModel\Oauth\Token as TokenResourceModel;
use Magedelight\Vendor\Helper\Oauth\Data as OauthHelper;

/**
 * Cron class for deleting expired OAuth tokens.
 */
class CleanExpiredTokens
{
    /**
     * @var TokenResourceModel
     */
    private $tokenResourceModel;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    /**
     * Initialize dependencies.
     *
     * @param TokenResourceModel $tokenResourceModel
     * @param OauthHelper $oauthHelper
     */
    public function __construct(
        TokenResourceModel $tokenResourceModel,
        OauthHelper $oauthHelper
    ) {
        $this->tokenResourceModel = $tokenResourceModel;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * Delete expired seller tokens.
     *
     * @return void
     */
    public function execute()
    {
        $this->tokenResourceModel->deleteExpiredTokens(
            $this->oauthHelper->getSellerTokenLifetime(),
            [AccountManagementInterface::USER_TYPE_SELLER]
        );
    }
}
