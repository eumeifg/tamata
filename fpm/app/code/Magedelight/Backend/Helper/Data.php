<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Data extends AbstractHelper
{
    const XML_PATH_USE_CUSTOM_SELLER_URL = 'md_backend/url/use_custom';

    /**
     * @var string
     */
    protected $_pageHelpUrl;

    /**
     * @var \Magento\Framework\App\Route\Config
     */
    protected $_routeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_locale;

    /**
     * @var \Magedelight\Backend\Model\UrlInterface
     */
    protected $_sellerPanelUrl;

    /**
     * @var \Magedelight\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magedelight\Backend\App\Area\FrontNameResolver
     */
    protected $_frontNameResolver;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magedelight\Backend\Model\UrlInterface $sellerPanelUrl
     * @param \Magedelight\Backend\Model\Auth $auth
     * @param \Magedelight\Backend\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Route\Config $routeConfig,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magedelight\Backend\Model\UrlInterface $sellerPanelUrl,
        \Magedelight\Backend\Model\Auth $auth,
        \Magedelight\Backend\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        parent::__construct($context);
        $this->_routeConfig = $routeConfig;
        $this->_locale = $locale;
        $this->_sellerPanelUrl = $sellerPanelUrl;
        $this->_auth = $auth;
        $this->_frontNameResolver = $frontNameResolver;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @return string
     */
    public function getPageHelpUrl()
    {
        if (!$this->_pageHelpUrl) {
            $this->setPageHelpUrl();
        }
        return $this->_pageHelpUrl;
    }

    /**
     * @param string|null $url
     * @return $this
     */
    public function setPageHelpUrl($url = null)
    {
        if ($url === null) {
            $request = $this->_request;
            $frontModule = $request->getControllerModule();
            if (!$frontModule) {
                $frontModule = $this->_routeConfig->getModulesByFrontName($request->getModuleName());
                if (empty($frontModule) === false) {
                    $frontModule = $frontModule[0];
                } else {
                    $frontModule = null;
                }
            }
            $url = 'http://www.magentocommerce.com/gethelp/';
            $url .= $this->_locale->getLocale() . '/';
            $url .= $frontModule . '/';
            $url .= $request->getControllerName() . '/';
            $url .= $request->getActionName() . '/';

            $this->_pageHelpUrl = $url;
        }
        $this->_pageHelpUrl = $url;

        return $this;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function addPageHelpUrl($suffix)
    {
        $this->_pageHelpUrl = $this->getPageHelpUrl() . $suffix;
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_sellerPanelUrl->getUrl($route, $params);
    }

    /**
     * @return int|bool
     */
    public function getCurrentUserId()
    {
        if ($this->_auth->getUser()) {
            return $this->_auth->getUser()->getId();
        }
        return false;
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
        return $this->getConfigValue('md_backend/general/enable');
    }

    /**
     * Decode filter string
     *
     * @param string $filterString
     * @return array
     */
    public function prepareFilterString($filterString)
    {
        $data = [];
        $filterString = base64_decode($filterString);
        parse_str($filterString, $data);
        array_walk_recursive(
            $data,
            // @codingStandardsIgnoreStart
            /**
             * Decodes URL-encoded string and trims whitespaces from the beginning and end of a string
             *
             * @param string $value
             */
            // @codingStandardsIgnoreEnd
            function (&$value) {
                $value = trim(rawurldecode($value));
            }
        );
        return $data;
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
     * Get backend start page URL
     *
     * @return string
     */
    public function getHomePageUrl()
    {
        return $this->_sellerPanelUrl->getRouteUrl('sellerhtml');
    }

    /**
     * Return Backend area front name
     *
     * @param bool $checkHost
     * @return bool|string
     */
    public function getAreaFrontName($checkHost = false)
    {
        return $this->_frontNameResolver->getFrontName($checkHost);
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionKey()
    {
            return 'ek-marketplace-m2';
    }
    
    /**
     *
     * @return string
     */
    public function getExtensionDisplayName()
    {
            return 'Rocket Bazaar Marketplace Base Suite';
    }
}
