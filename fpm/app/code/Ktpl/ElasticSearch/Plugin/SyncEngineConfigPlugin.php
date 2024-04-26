<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_ElasticSearch
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\ElasticSearch\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class SyncEngineConfigPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class SyncEngineConfigPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * SyncEngineConfigPlugin constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigFactory $configFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configFactory = $configFactory;
    }

    /**
     * Save catalog search engine
     * @throws \Exception
     */
    public function beforeGet()
    {
        $native = $this->scopeConfig->getValue('catalog/search/engine', ScopeInterface::SCOPE_STORE);
        $our = $this->scopeConfig->getValue(Config::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);

        if ($native != $our) {
            $config = $this->configFactory->create();

            $config->setDataByPath('catalog/search/engine', $our);
            $config->save();
        }
    }
}
