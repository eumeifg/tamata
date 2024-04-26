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
namespace Magedelight\Backend\Model\Session;

use Magedelight\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Config;

/**
 * Magento Backend session configuration
 */
class SellerConfig extends Config
{
    /**
     * Configuration for seller session name
     */
    const SESSION_NAME_SELLER = 'seller';

    /**
     * @var FrontNameResolver
     */
    protected $_frontNameResolver;

    /**
     * @var \Magedelight\Backend\App\BackendAppList
     */
    private $backendAppList;

    /**
     * @var \Magento\Backend\Model\UrlFactory
     */
    private $backendUrlFactory;

    /**
     * @param \Magento\Framework\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\StringUtils $stringHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Filesystem $filesystem
     * @param DeploymentConfig $deploymentConfig
     * @param string $scopeType
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     * @param FrontNameResolver $frontNameResolver
     * @param \Magento\Backend\Model\UrlFactory $backendUrlFactory
     * @param string $lifetimePath
     * @param string $sessionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ValidatorFactory $validatorFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\StringUtils $stringHelper,
        \Magento\Framework\App\RequestInterface $request,
        Filesystem $filesystem,
        DeploymentConfig $deploymentConfig,
        $scopeType,
        \Magedelight\Backend\App\BackendAppList $backendAppList,
        FrontNameResolver $frontNameResolver,
        \Magedelight\Backend\Model\UrlFactory $backendUrlFactory,
        $lifetimePath = self::XML_PATH_COOKIE_LIFETIME,
        $sessionName = self::SESSION_NAME_SELLER
    ) {
        parent::__construct(
            $validatorFactory,
            $scopeConfig,
            $stringHelper,
            $request,
            $filesystem,
            $deploymentConfig,
            $scopeType,
            $lifetimePath
        );
        $this->_frontNameResolver = $frontNameResolver;
        $this->backendAppList = $backendAppList;
        $this->backendUrlFactory = $backendUrlFactory;
        $sellerPath = $this->extractSellerPath();
        $this->setCookiePath($sellerPath);
        $this->setName($sessionName);
        $this->setCookieSecure($this->_httpRequest->isSecure());
    }

    /**
     * Determine the admin path
     *
     * @return string
     */
    private function extractSellerPath()
    {
        $backendApp = $this->backendAppList->getCurrentApp();
        $cookiePath = null;
        $baseUrl = parse_url($this->backendUrlFactory->create()->getBaseUrl(), PHP_URL_PATH);
        if (!$backendApp) {
            $cookiePath = $baseUrl . $this->_frontNameResolver->getFrontName();
            return $cookiePath;
        }
        //In case of application authenticating through the seller login, the script name should be removed
        //from the path, because application has own script.
        $baseUrl = \Magento\Framework\App\Request\Http::getUrlNoScript($baseUrl);
        $cookiePath = $baseUrl . $backendApp->getCookiePath();
        return $cookiePath;
    }

    /**
     * Set session cookie lifetime to session duration
     *
     * @return $this
     */
    protected function configureCookieLifetime()
    {
        return $this->setCookieLifetime(0);
    }
}
