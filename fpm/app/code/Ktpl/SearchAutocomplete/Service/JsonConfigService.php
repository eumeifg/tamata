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

namespace Ktpl\SearchAutocomplete\Service;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ktpl\SearchAutocomplete\Api\Repository\IndexRepositoryInterface;
use Ktpl\SearchAutocomplete\Model\Config;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class JsonConfigService
 *
 * @package Ktpl\SearchAutocomplete\Service
 */
class JsonConfigService
{
    /**
     * Autocomplete
     */
    const AUTOCOMPLETE = 'autocomplete';

    /**
     * Typeahead
     */
    const TYPEAHEAD = 'typeahead';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @var QueryCollectionFactory
     */
    private $queryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * JsonConfigService constructor.
     *
     * @param Filesystem $fs
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param IndexRepositoryInterface $indexRepository
     * @param SearchHelper $searchHelper
     * @param QueryCollectionFactory $queryCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Filesystem $fs,
        ScopeConfigInterface $scopeConfig,
        Config $config,
        IndexRepositoryInterface $indexRepository,
        SearchHelper $searchHelper,
        QueryCollectionFactory $queryCollectionFactory,
        StoreManagerInterface $storeManager
    )
    {
        $this->fs = $fs;
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->indexRepository = $indexRepository;
        $this->searchHelper = $searchHelper;
        $this->queryCollectionFactory = $queryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Ensure json config option
     *
     * @param $option
     * @return $this
     */
    public function ensure($option)
    {
        $path = $this->fs->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath();
        $filePath = $path . $option . '.json';

        if (!$this->isOptionEnabled($option)) {
            @unlink($filePath);

            return $this;
        }

        $config = $this->generate($option);

        @file_put_contents($filePath, \Zend_Json::encode($config));

        return $this;
    }

    /**
     * Check if option is enabled
     *
     * @return bool
     */
    private function isOptionEnabled($option)
    {
        switch ($option) {
            case self::AUTOCOMPLETE:
                return $this->config->isFastMode();
                break;
            case self::TYPEAHEAD:
                return $this->config->isTypeAheadEnabled();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Generate json config option
     *
     * @return array
     */
    public function generate($option)
    {
        switch ($option) {
            case self::AUTOCOMPLETE:
                return $this->generateAutocompleteConfig();
                break;
            case self::TYPEAHEAD:
                return $this->generateTypeaheadConfig();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * General search autocomplete configurations
     *
     * @return array
     */
    private function generateAutocompleteConfig()
    {
        $config = [
            'engine' => $this->scopeConfig->getValue('search/engine/engine'),
            'is_optimize_mobile' => $this->config->isOptimizeMobile(),
            'is_show_cart_button' => $this->config->isShowCartButton(),
            'is_show_image' => $this->config->isShowImage(),
            'is_show_price' => $this->config->isShowPrice(),
            'is_show_rating' => $this->config->isShowRating(),
            'is_show_sku' => $this->config->isShowSku(),
            'is_show_short_description' => $this->config->isShowShortDescription(),
            'textAll' => __('Show all %1 results →', "%s")->render(),
            'textEmpty' => __('Sorry, nothing found for "%1".', "%s")->render(),
            'urlAll' => $this->getStoreSpecificAllUrls(),
        ];

        foreach ($this->storeManager->getStores() as $store) {
            foreach ($this->indexRepository->getIndices() as $index) {
                $identifier = $index->getIdentifier();

                if (!$this->config->getIndexOptionValue($identifier, 'is_active')) {
                    continue;
                }

                if ($identifier == 'magento_catalog_categoryproduct' || $identifier == 'magento_search_query') {
                    continue;
                }

                $index->addData($this->config->getIndexOptions($identifier));

                $config['indexes'][$store->getId()][$identifier] = [
                    'title' => __($index->getTitle())->render(),
                    'identifier' => $identifier,
                    'order' => $index->getOrder(),
                    'limit' => $index->getLimit(),
                ];
            }
        }

        return $config;
    }

    /**
     * Get store specific all urls
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreSpecificAllUrls()
    {
        $result = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeCode = $store->getCode();
            $baseUrl = $this->storeManager->getStore($store->getId())->getBaseUrl();
            $allResultsUrl = $this->searchHelper->getResultUrl("");

            if (strrpos($allResultsUrl, $baseUrl) === false && strrpos($baseUrl, '/' . $storeCode . '/') !== false) {
                $baseUrl = rtrim($baseUrl, '/');
                $allResultsUrlArray = explode('/', $baseUrl) + explode('/', $allResultsUrl);
                $allResultsUrl = implode('/', $allResultsUrlArray);
            }

            $result[$store->getId()] = $allResultsUrl;
        }

        return $result;
    }

    /**
     * Generate type ahead configurations
     *
     * @return array
     */
    private function generateTypeaheadConfig()
    {
        $config = [];
        $config['engine'] = false;

        $collection = $this->queryCollectionFactory->create();

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'suggest' => new \Zend_Db_Expr('MAX(query_text)'),
                'suggest_key' => new \Zend_Db_Expr('substring(query_text,1,2)'),
                'popularity' => new \Zend_Db_Expr('MAX(popularity)'),
            ])
            ->where('num_results > 0')
            ->where('display_in_terms = 1')
            ->where('is_active = 1')
            ->where('popularity > 10 ')
            ->where('CHAR_LENGTH(query_text) > 3')
            ->group(new \Zend_Db_Expr('substring(query_text,1,6)'))
            ->group(new \Zend_Db_Expr('substring(query_text,1,2)'))
            ->order('suggest_key ' . \Magento\Framework\DB\Select::SQL_ASC)
            ->order('popularity ' . \Magento\Framework\DB\Select::SQL_DESC);

        foreach ($collection as $suggestion) {
            $config[strtolower($suggestion['suggest_key'])][] = strtolower($suggestion['suggest']);
        }

        return $config;
    }
}
