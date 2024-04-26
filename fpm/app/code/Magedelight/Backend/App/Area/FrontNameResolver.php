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
namespace Magedelight\Backend\App\Area;

/**
 * Description of FrontNameResolver
 *
 * @author Rocket Bazaar Core Team
 */
use Magedelight\Backend\Setup\ConfigOptionsList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class FrontNameResolver implements \Magento\Framework\App\Area\FrontNameResolverInterface
{
    const XML_PATH_USE_CUSTOM_SELLER_PATH = 'md_backend/url/use_custom_path';

    const XML_PATH_CUSTOM_SELLER_PATH = 'md_backend/url/custom_path';

    const XML_PATH_USE_CUSTOM_SELLER_URL = 'md_backend/url/use_custom';

    const XML_PATH_CUSTOM_SELLER_URL = 'md_backend/url/custom';

    /**
     * Seller area code
     */
    const AREA_CODE = 'sellerhtml';

    /**
     * @var array
     */
    protected $standardPorts = ['http' => '80', 'https' => '443'];

    /**
     * @var string
     */
    protected $defaultFrontName;

    /**
     * @var \Magedelight\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param \Magedelight\Backend\App\Config $config
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magedelight\Backend\App\Config $config,
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->defaultFrontName = $deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_VENDOR_FRONTNAME);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve area front name
     *
     * @param bool $checkHost If true, verify front name is valid for this url (hostname is correct)
     * @return string|bool
     */
    public function getFrontName($checkHost = false)
    {
        if ($checkHost && !$this->isHostSellerPanel()) {
            return false;
        }
        $isCustomPathUsed = (bool)(string)$this->config->getValue(self::XML_PATH_USE_CUSTOM_SELLER_PATH);
        if ($isCustomPathUsed) {
            return (string)$this->config->getValue(self::XML_PATH_CUSTOM_SELLER_PATH);
        }
        return $this->defaultFrontName;
    }

    /**
     * Return whether the host from request is the seller panel host
     *
     * @return bool
     */
    public function isHostSellerPanel()
    {
        if ($this->scopeConfig->getValue(self::XML_PATH_USE_CUSTOM_SELLER_URL, ScopeInterface::SCOPE_STORE)) {
            $sellerPanelUrl = $this->scopeConfig->getValue(self::XML_PATH_CUSTOM_SELLER_URL, ScopeInterface::SCOPE_STORE);
        } else {
            $sellerPanelUrl = $this->scopeConfig->getValue(Store::XML_PATH_UNSECURE_BASE_URL, ScopeInterface::SCOPE_STORE);
        }
        
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        return stripos($this->getHostWithPort($sellerPanelUrl), $host) !== false;
    }

    /**
     * Get host with port
     *
     * @param string $url
     * @return mixed|string
     */
    private function getHostWithPort($url)
    {
        $scheme = parse_url(trim($url), PHP_URL_SCHEME);
        $host = parse_url(trim($url), PHP_URL_HOST);
        $port = parse_url(trim($url), PHP_URL_PORT);
        if (!$port) {
            $port = isset($this->standardPorts[$scheme]) ? $this->standardPorts[$scheme] : null;
        }
        return isset($port) ? $host . ':' . $port : $host;
    }
}
