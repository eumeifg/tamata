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
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;

/**
 * Class ModuleStatusPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class ModuleStatusPlugin
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var State
     **/
    private $state;

    /**
     * ModuleStatusPlugin constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigFactory $configFactory
     * @param State $state
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfig,
        ConfigFactory $configFactory,
        State $state
    )
    {
        $this->deploymentConfig = $deploymentConfig;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
        $this->configFactory = $configFactory;
    }

    /**
     * Save module config data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSaveConfig()
    {
        $configData = $this->deploymentConfig->getConfigData();
        if (isset($configData['modules']) && isset($configData['modules']['Ktpl_ElasticSearch'])) {
            if ($configData['modules']['Ktpl_ElasticSearch'] == 0) {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

                $config = $this->configFactory->create();
                $config->setDataByPath('catalog/search/engine', 'mysql');
                $config->save();
            }
        }
    }
}
