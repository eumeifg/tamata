<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_User
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\User\Helper;

/**
 * Description of Data
 *
 * @author Rocket Bazaar Core Team
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Configuration path to expiration period of reset password link
     */
    const XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD = 'admin/security/password_reset_link_expiration_period';

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->config = $config;
        $this->mathRandom = $mathRandom;
        parent::__construct($context);
    }

    /**
     * Generate unique token for reset password confirmation link
     *
     * @return string
     */
    public function generateResetPasswordLinkToken()
    {
        return $this->mathRandom->getUniqueHash();
    }

    /**
     * Retrieve customer reset password link expiration period in days
     *
     * @return int
     */
    public function getResetPasswordLinkExpirationPeriod()
    {
        return (int)$this->config->getValue(self::XML_PATH_ADMIN_RESET_PASSWORD_LINK_EXPIRATION_PERIOD);
    }
    
    /**
     *
     * @param string $path
     * @return boolean
     */
    public function getConfigValue($path = '', $storeId = null)
    {
        return ($path)?$this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId):'';
    }
    
    /**
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getConfigValue('vendorauthorization/general/enable');
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionKey()
    {
            return 'ek-rb-user';
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionDisplayName()
    {
            return 'User Role Management Add-on';
    }
}
