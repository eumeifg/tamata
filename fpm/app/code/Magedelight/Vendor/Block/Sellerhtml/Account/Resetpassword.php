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
namespace Magedelight\Vendor\Block\Sellerhtml\Account;

use Magedelight\Backend\Model\Url;
use Magedelight\Backend\Block\Template;

/**
 * Vendor reset password form
 * @author Rocket Bazaar Core Team
 * Created at 13 Feb, 2016 7:15:31 PM
 */
class Resetpassword extends Template
{
    /**
     * @var Url
     */
    protected $vendorUrl;

    /**
     * @param Template\Context $context
     * @param Url $vendorUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $vendorUrl,
        array $data = []
    ) {
        $this->vendorUrl = $vendorUrl;
        parent::__construct($context, $data);
    }
    
    /**
     * Get login URL
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->vendorUrl->getLoginUrl();
    }
}
