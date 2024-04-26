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
namespace Magedelight\Vendor\Block\Sellerhtml\Form;

use Magedelight\Backend\Block\Template;

class Login extends Template
{

    const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    const XML_PATH_GENERAL_COUNTRY_ALLCOUNTRY = 'general/country/allow';
    
    /**
     * @var int
     */
    private $_username = -1;
    
    /**
     * @var \Magedelight\Backend\Model\Url
     */
    protected $_vendorUrl;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
     /**
      * @var \Magedelight\Backend\Model\Auth\Session
      */
    protected $authSession;

    /**
     * @param \Magedelight\Backend\Block\Template\Context $context
     * @param \Magedelight\Backend\Model\Auth\Session $authSession
     * @param \Magedelight\Backend\Model\Url $vendorUrl
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magedelight\Backend\Model\Url $vendorUrl,
        array $data = []
    ) {
        $this->_vendorUrl = $vendorUrl;
        $this->scopeConfig = $context->getScopeConfig();
        $this->authSession = $authSession;
        parent::__construct($context, $data);
    }

    public function canShowLoginForm()
    {
        return !$this->authSession->isLoggedIn();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_vendorUrl->getLoginPostUrl();
    }

    /**
     * Retrieve password forgotten url
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->_vendorUrl->getForgotPasswordUrl();
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */
    public function getUsername()
    {
        if (-1 === $this->_username && $this->authSession->isLoggedIn()) {
            $this->_username = $this->authSession->getUser()->getEmail(true);
            return $this->_username;
        }
    }

    /**
     * Check if autocomplete is disabled on storefront
     *
     * @return bool
     */
    public function isAutocompleteDisabled()
    {
        return (bool)!$this->_scopeConfig->getValue(
            \Magedelight\Vendor\Model\Form::XML_PATH_ENABLE_AUTOCOMPLETE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function getVendorLoggedInData()
    {
        $result = $this->vendorSession->getVendorDataAsLoggedIn();
        return $result;
    }
    
    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getRegisterPostActionUrl()
    {
        return $this->_vendorUrl->getEmailVerificationUrl();
    }
    
    public function getDefaultCountry()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_COUNTRY_DEFAULT, $storeScope);
    }

    public function getAllowCountry()
    {
         $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_COUNTRY_ALLCOUNTRY, $storeScope);
    }
}
