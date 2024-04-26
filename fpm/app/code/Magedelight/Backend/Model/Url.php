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
namespace Magedelight\Backend\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\HostChecker;

class Url extends \Magento\Framework\Url implements \Magedelight\Backend\Model\UrlInterface
{

    /**
     * Whether to use a security key in the backend
     *
     * @bug Currently, this constant is slightly misleading: it says "form key", but in fact it is used by URLs, too
     */
    const XML_PATH_USE_SECURE_KEY = 'admin/security/use_form_key';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Route for vendor account login page
     */
    const ROUTE_VENDOR_LOGIN = 'rbvendor/index/index';

    /**
     * Config name for Redirect Vendor to Account Dashboard after Logging in setting
     */
    const XML_PATH_VENDOR_STARTUP_REDIRECT_TO_DASHBOARD = 'rbvendor/account/index';

    /**
     * Query param name for last url visited
     */
    const REFERER_QUERY_PARAM_NAME = 'referer';

    /**
     * @var \Magedelight\Backend\Helper\Data
     */
    protected $_backendHelper;
    
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;
    
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_scope;
    
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;
    
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;
    
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
    /**
     * Authentication session
     *
     * @var \Magedelight\Backend\Model\Auth\Session
     */
    protected $_session;
    
    protected $_allowPrefix = true;



    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo,
        \Magento\Framework\Url\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Url\RouteParamsResolverFactory $routeParamsResolverFactory,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\RouteParamsPreprocessorInterface $routeParamsPreprocessor,
        $scopeType,
        \Magedelight\Backend\Helper\Data $backendHelper,
        StoreManagerInterface $storeManager,
        EncoderInterface $urlEncoder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magedelight\Backend\Model\Auth\Session $authSession,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = [],
        HostChecker $hostChecker = null,
        Json $serializer = null
    ) {
        parent::__construct(
            $routeConfig,
            $request,
            $urlSecurityInfo,
            $scopeResolver,
            $session,
            $sidResolver,
            $routeParamsResolverFactory,
            $queryParamsResolver,
            $scopeConfig,
            $routeParamsPreprocessor,
            $scopeType,
            $data,
            $hostChecker,
            $serializer
        );
        $this->request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->urlEncoder = $urlEncoder;
        $this->storeManager = $storeManager;
        $this->_cache = $cache;
        $this->_storeFactory = $storeFactory;
        $this->formKey = $formKey;
        $this->_encryptor = $encryptor;
        $this->_session = $authSession;
        $this->_backendHelper = $backendHelper;
    }

    /**
     * Retrieve vendor login url
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl(self::ROUTE_VENDOR_LOGIN, $this->getLoginUrlParams());
    }
    
    /**
     * Custom logic to retrieve Urls
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        /* For API urls. */
        if (!empty($routeParams) && is_array($routeParams)) {
            if (array_key_exists('clear_prefix', $routeParams) && $routeParams['clear_prefix']) {
                $this->clearPrefix();
                unset($routeParams['clear_prefix']);
                $this->turnOffSecretKey();
            } elseif (array_key_exists('_nosid', $routeParams) && $routeParams['_nosid']) {
                unset($routeParams['_nosid']);
                $this->turnOffSecretKey();
            } else {
                $this->_allowPrefix = true;
                $this->turnOnSecretKey();
            }
        }
        /* For API urls. */
        
        if (filter_var($routePath, FILTER_VALIDATE_URL)) {
            return $routePath;
        }

        $cacheSecretKey = false;
        if (isset($routeParams['_cache_secret_key'])) {
            unset($routeParams['_cache_secret_key']);
            $cacheSecretKey = true;
        }
        $result = parent::getUrl($routePath, $routeParams);
        if (!$this->useSecretKey()) {
            return $result;
        }

        $this->getRouteParamsResolver()->unsetData('route_params');
        $this->_setRoutePath($routePath);
        $extraParams = $this->getRouteParamsResolver()->getRouteParams();
        $routeName = $this->_getRouteName('*');
        $controllerName = $this->_getControllerName(self::DEFAULT_CONTROLLER_NAME);
        $actionName = $this->_getActionName(self::DEFAULT_ACTION_NAME);

        if (!isset($routeParams[self::SECRET_KEY_PARAM_NAME])) {
            if (!is_array($routeParams)) {
                $routeParams = [];
            }
            $secretKey = $cacheSecretKey
                ? "\${$routeName}/{$controllerName}/{$actionName}\$"
                : $this->getSecretKey($routeName, $controllerName, $actionName);
            $routeParams[self::SECRET_KEY_PARAM_NAME] = $secretKey;
        }

        if (!empty($extraParams)) {
            $routeParams = array_merge($extraParams, $routeParams);
        }

        return parent::getUrl("{$routeName}/{$controllerName}/{$actionName}", $routeParams);
    }

    /**
     * Retrieve parameters of vendor login url
     *
     * @return array
     */
    public function getLoginUrlParams()
    {
        $params = [];
        $referer = $this->request->getParam(self::REFERER_QUERY_PARAM_NAME);
        
        if (!$referer
            && !$this->_scopeConfig->isSetFlag(
                self::XML_PATH_VENDOR_STARTUP_REDIRECT_TO_DASHBOARD,
                ScopeInterface::SCOPE_STORE
            )
        ) {
            $referer = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $referer = $this->urlEncoder->encode($referer);
        }

        if ($referer) {
            $params = [self::REFERER_QUERY_PARAM_NAME => $referer];
        }

        return $params;
    }

    /**
     * Retrieve vendor login POST URL
     *
     * @return string
     */
    public function getLoginPostUrl()
    {
        $params = [];
        if ($this->request->getParam(self::REFERER_QUERY_PARAM_NAME)) {
            $params = [
                self::REFERER_QUERY_PARAM_NAME => $this->request->getParam(self::REFERER_QUERY_PARAM_NAME),
            ];
        }
        return $this->getUrl('rbvendor/account/loginPost', $params);
    }

    /**
     * Retrieve vendor logout url
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        return $this->getUrl('rbvendor/account/logout');
    }

    /**
     * Retrieve vendor dashboard url
     *
     * @return string
     */
    public function getDashboardUrl()
    {
        return $this->getUrl('rbvendor/account/dashboard');
    }

    /**
     * Retrieve vendor account page url
     *
     * @return string
     */
    public function getAccountUrl()
    {
        return $this->getUrl('rbvendor/account');
    }

    /**
     * Retrieve vendor register form url
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->getUrl('rbvendor/account/register');
    }

    /**
     * Retrieve vendor register form post url
     *
     * @return string
     */
    public function getRegisterPostUrl()
    {
        return $this->getUrl('rbvendor/account/registerPost');
    }
    /**
     * Retrieve vendor register form post url
     *
     * @return string
     */
    public function getEmailVerificationUrl()
    {
        return $this->getUrl('rbvendor/account/sendVerification');
    }

    /**
     * Retrieve vendor account edit form url
     *
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl('rbvendor/account/edit');
    }

    /**
     * Retrieve vendor edit POST URL
     *
     * @return string
     */
    public function getEditPostUrl()
    {
        return $this->getUrl('rbvendor/account/editpost');
    }

    /**
     * Retrieve url of forgot password page
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->getUrl('rbvendor/account/forgotpassword');
    }

    /**
     * Retrieve confirmation URL for Email
     *
     * @param string $email
     * @return string
     */
    public function getEmailConfirmationUrl($email = null)
    {
        return $this->getUrl('rbvendor/account/confirmation', ['email' => $email]);
    }
    
    /**
     * Retrieve media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
    }

    public function findFirstAvailableMenu()
    {
    }
    
    public function clearPrefix()
    {
        $this->_allowPrefix = false;
    }
    
    /**
     * Retrieve action path.
     * Add backend area front name as a prefix to action path
     *
     * @return string
     */
    protected function _getActionPath()
    {
        $path = parent::_getActionPath();
        if ($path && $this->_allowPrefix) {
            if ($this->getAreaFrontName()) {
                $path = $this->getAreaFrontName() . '/' . $path;
            }
        }
        return $path;
    }

    /**
     * Return vendor area front name, defined in configuration
     *
     * @return string
     */
    public function getAreaFrontName()
    {
        if (!$this->_getData('area_front_name')) {
            $this->setData('area_front_name', $this->_backendHelper->getAreaFrontName());
        }
        return $this->_getData('area_front_name');
    }

    public function getDirectUrl($url, $params = [])
    {
    }

    public function getRedirectUrl($url)
    {
    }

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null)
    {
        $salt = $this->formKey->getFormKey();
        $request = $this->_getRequest();
        if (!$routeName) {
            if ($request->getBeforeForwardInfo('route_name') !== null) {
                $routeName = $request->getBeforeForwardInfo('route_name');
            } else {
                $routeName = $request->getRouteName();
            }
        }
        if (!$controller) {
            if ($request->getBeforeForwardInfo('controller_name') !== null) {
                $controller = $request->getBeforeForwardInfo('controller_name');
            } else {
                $controller = $request->getControllerName();
            }
        }
        if (!$action) {
            if ($request->getBeforeForwardInfo('action_name') !== null) {
                $action = $request->getBeforeForwardInfo('action_name');
            } else {
                $action = $request->getActionName();
            }
        }
        $secret = $routeName . $controller . $action . $salt;
        return $this->_encryptor->getHash($secret);
    }

    public function getStartupPageUrl()
    {
        return $this->getUrl('rbvendor/account/dashboard');
    }

    public function getUseSession()
    {
    }

    /**
     * Refresh admin menu cache etc.
     *
     * @return void
     */
    public function renewSecretUrls()
    {
        $this->_cache->clean([\Magento\Backend\Block\Menu::CACHE_TAGS]);
    }
    
    /**
     * Get scope for the url instance
     *
     * @return \Magento\Store\Model\Store
     */
    protected function _getScope()
    {
        if (!$this->_scope) {
            $this->_scope = $this->_storeFactory->create(
                [
                    'url' => $this,
                    'data' => ['code' => 'seller', 'force_disable_rewrites' => false, 'disable_store_in_url' => true],
                ]
            );
        }
        return $this->_scope;
    }

    /**
     * Retrieve auth session
     *
     * @return \Magedelight\Vendor\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Set custom auth session
     *
     * @param \Magedelight\Vendor\Model\Session $session
     * @return $this
     */
    public function setSession(\Magedelight\Vendor\Model\Session $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Disable secret key using
     *
     * @return $this
     */
    public function turnOffSecretKey()
    {
        $this->setNoSecret(true);
        return $this;
    }

     /**
      * Enable secret key using
      *
      * @return $this
      */
    public function turnOnSecretKey()
    {
        $this->setNoSecret(false);
        return $this;
    }

    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function useSecretKey()
    {
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_USE_SECURE_KEY) && !$this->getNoSecret();
    }
    
    /**
     * Get cache id for config path
     *
     * @param string $path
     * @return string
     */
    protected function _getConfigCacheId($path)
    {
        return 'seller/' . $path;
    }
}
