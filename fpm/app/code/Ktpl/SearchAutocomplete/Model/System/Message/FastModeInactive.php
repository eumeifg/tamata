<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchAutocomplete
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchAutocomplete\Model\System\Message;

use Magento\Framework\AuthorizationInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Filesystem\DirectoryList;
use Ktpl\SearchAutocomplete\Model\Config;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class FastModeInactive
 *
 * @package Ktpl\SearchAutocomplete\Model\System\Message
 */
class FastModeInactive implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;
    /**
     * @var Data
     */
    protected $backendHelper;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var DirectoryList
     */
    private $config;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var $engine
     */
    private $engine;

    /**
     * FastModeInactive constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param Data $backendHelper
     * @param DirectoryList $directoryList
     * @param Config $config
     * @param ObjectManagerInterface $objectManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AuthorizationInterface $authorization,
        Data $backendHelper,
        DirectoryList $directoryList,
        Config $config,
        ObjectManagerInterface $objectManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->authorization = $authorization;
        $this->backendHelper = $backendHelper;
        $this->directoryList = $directoryList;
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('ktpl_searchAutocomplete_missing_autocomplete_json');
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return !$this->_getAutocompleteJsonExists() && $this->isFastModeEnabled();
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    protected function _getAutocompleteJsonExists()
    {
        $autocompleteJsonExists = true;

        if (!file_exists($this->directoryList->getRoot() . '/app/etc/autocomplete.json')) {
            $autocompleteJsonExists = false;
        }

        return $autocompleteJsonExists;
    }

    /**
     * Check if fast mode enabled
     *
     * @return bool
     */
    private function isFastModeEnabled()
    {
        return $this->config->isFastMode();
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $message = __('Ktpl Search Autocomplete Fast Mode doesn`t work. ');
        if (!$this->externalEngineRunning()) {
            $message .= __('Fast Mode supports external engine only, please use %1.', $this->getSearchEngineData($this->engine));
        } else {
            $message .= __('Autocomplete is missing config file. ');
            $message .= __('To generate it please disable and enable again <a href="%1">fast mode</a> and run search reindex.', $this->getLink());
        }

        return $message;
    }

    /**
     * Check if external plugin running
     *
     * @return bool
     */
    private function externalEngineRunning()
    {
        if ($this->getActiveSearchEngine() == 'mysql2') {
            $this->engine = $this->getAvailableExternalEngine();
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get active search engine data
     *
     * @return string
     */
    private function getActiveSearchEngine()
    {
        $activeEngine = 'mysql2';
        $engine = $this->scopeConfig->getValue('search/engine/engine');
        if ($engine == 'elastic') {
            try {
                $engine = $this->objectManager->create('Ktpl\SearchElastic\Model\Engine');
                $out = '';
                $result = $engine->status($out);
                if ($result) {
                    $activeEngine = 'elastic';
                }
            } catch (\Exception $e) {
            }
        } elseif ($engine == 'sphinx') {
            try {
                $engine = $this->objectManager->get('Ktpl\SearchSphinx\Model\Engine');
                $out = '';
                $result = $engine->status($out);
                if ($result) {
                    $activeEngine = 'sphinx';
                }
            } catch (\Exception $e) {
            }
        }

        return $activeEngine;
    }

    /**
     * Get available external engine
     *
     * @return string
     */
    private function getAvailableExternalEngine()
    {
        $engine = 'mysql2';
        if (class_exists('Ktpl\SearchElastic\Model\Engine')) {
            $engine = 'elastic';
        } else if (class_exists('Ktpl\SearchSphinx\Model\Engine')) {
            $engine = 'sphinx';
        }

        return $engine;
    }

    /**
     * Get search engine data
     *
     * @param $key
     * @return mixed
     */
    private function getSearchEngineData($key)
    {
        $engines = [
            'mysql2' => 'Built-in Engine',
            'sphinx' => 'External Sphinx Engine',
            'elastic' => 'Elasticsearch Engine',
        ];

        return $engines[$key];
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->backendHelper->getUrl('admin/system_config/edit/section/searchautocomplete', []);
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
    }
}
