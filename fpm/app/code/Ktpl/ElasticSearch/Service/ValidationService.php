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

namespace Ktpl\ElasticSearch\Service;

use Magento\Framework\Module\Manager;
use Magento\Framework\Module\ModuleListInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class ValidationService
 *
 * @package Ktpl\ElasticSearch\Service
 */
class ValidationService extends AbstractValidator
{
    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Config
     */
    private $config;

    /**
     * ValidationService constructor.
     *
     * @param Manager $moduleManager
     * @param ModuleListInterface $moduleList
     * @param Config $config
     */
    public function __construct(
        Manager $moduleManager,
        ModuleListInterface $moduleList,
        Config $config
    )
    {
        $this->moduleManager = $moduleManager;
        $this->moduleList = $moduleList;
        $this->config = $config;
    }

    /**
     * Check known conflicts
     */
    public function testKnownConflicts()
    {
        $known = ['Mageworks_SearchSuite', 'Magento_Solr', 'Magento_ElasticSearch'];

        foreach ($known as $moduleName) {
            if ($this->moduleManager->isEnabled($moduleName)) {
                $this->addError('Please disable {0} module.', [$moduleName]);
            }
        }
    }

    /**
     * Check possible conflicts
     */
    public function testPossibleConflicts()
    {
        $exceptions = ['Magento_Search', 'Magento_CatalogSearch'];

        foreach ($this->moduleList->getAll() as $module) {
            $moduleName = $module['name'];

            if (in_array($moduleName, $exceptions)) {
                continue;
            }

            if (stripos($moduleName, 'ktpl') !== false) {
                continue;
            }

            if (stripos($moduleName, 'search') !== false && $this->moduleManager->isEnabled($moduleName)) {
                $this->addWarning("Possible conflict with {0} module.", [$moduleName]);
            }
        }
    }

    /**
     * Check search engine
     */
    public function testSearchEngine()
    {
        if ($this->config->getEngine() != $this->config->getStoreEngine()) {
            $this->addWarning("Your configuration contains different search engines .
                Please check your core_config_data table and use catalog/search/engine the same as search/engine/engine");
        }
    }
}
