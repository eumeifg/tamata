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
namespace Magedelight\User\Helper\Backend;

/**
 * Description of Data
 *
 * @author Rocket Bazaar Core Team
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';

    /**
     * @var string
     */
    protected $pageHelpUrl;

    /**
     * @var \Magento\Framework\App\Route\Config
     */
    protected $routeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $locale;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $auth;

    /**
     * @var \Magento\Backend\App\Area\FrontNameResolver
     */
    protected $frontNameResolver;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\Locale\ResolverInterface $locale
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Route\Config $routeConfig,
        \Magento\Framework\Locale\ResolverInterface $locale,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\App\Area\FrontNameResolver $frontNameResolver,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        parent::__construct($context);
        $this->routeConfig = $routeConfig;
        $this->locale = $locale;
        $this->backendUrl = $backendUrl;
        $this->auth = $auth;
        $this->frontNameResolver = $frontNameResolver;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @return string
     */
    public function getPageHelpUrl()
    {
        if (!$this->pageHelpUrl) {
            $this->setPageHelpUrl();
        }
        return $this->pageHelpUrl;
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
                $frontModule = $this->routeConfig->getModulesByFrontName($request->getModuleName());
                if (empty($frontModule) === false) {
                    $frontModule = $frontModule[0];
                } else {
                    $frontModule = null;
                }
            }
            $url = 'http://www.magentocommerce.com/gethelp/';
            $url .= $this->locale->getLocale() . '/';
            $url .= $frontModule . '/';
            $url .= $request->getControllerName() . '/';
            $url .= $request->getActionName() . '/';

            $this->pageHelpUrl = $url;
        }
        $this->pageHelpUrl = $url;

        return $this;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function addPageHelpUrl($suffix)
    {
        $this->pageHelpUrl = $this->getPageHelpUrl() . $suffix;
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->backendUrl->getUrl($route, $params);
    }

    /**
     * @return int|bool
     */
    public function getCurrentUserId()
    {
        if ($this->auth->getUser()) {
            return $this->auth->getUser()->getId();
        }
        return false;
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
        return $this->backendUrl->getRouteUrl('rbvendor/user');
    }

    /**
     * Return Backend area front name
     *
     * @param bool $checkHost
     * @return bool|string
     */
    public function getAreaFrontName($checkHost = false)
    {
        return $this->frontNameResolver->getFrontName($checkHost);
    }
}
