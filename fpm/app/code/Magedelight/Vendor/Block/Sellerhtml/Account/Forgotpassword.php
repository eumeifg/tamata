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
 * @author Rocket Bazaar Core Team
 *  Created at 16 March, 2016 3:04 PM
 */
class Forgotpassword extends Template
{

    /**
     * @var \Magedelight\Vendor\Helper\Data
     */
    protected $_helper;

    /**
     * @var Url
     */
    protected $vendorUrl;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param Url $vendorUrl
     * @param \Magedelight\Vendor\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Url $vendorUrl,
        \Magedelight\Vendor\Helper\Data $helper,
        array $data = []
    ) {
        $this->vendorUrl = $vendorUrl;
        $this->_helper = $helper;
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
    
    public function getAdminEmail()
    {
        return $this->_helper->getConfigValue('contact/email/recipient_email');
    }
}
