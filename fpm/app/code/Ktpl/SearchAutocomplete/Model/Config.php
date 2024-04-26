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

namespace Ktpl\SearchAutocomplete\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as QueryCollectionFactory;
use Ktpl\ElasticSearch\Service\CompatibilityService;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Ktpl\SearchAutocomplete\Model
 */
class Config
{
    /**
     * 1column layout option
     */
    const LAYOUT_1_COLUMN = '1column';

    /**
     * 2columns layout option
     */
    const LAYOUT_2_COLUMNS = '2columns';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var QueryCollectionFactory
     */
    protected $queryCollectionFactory;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param QueryCollectionFactory $queryCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        QueryCollectionFactory $queryCollectionFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->queryCollectionFactory = $queryCollectionFactory;
    }

    /**
     * Check if show product image
     *
     * @return bool
     */
    public function isShowImage()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/show_image');
    }

    /**
     * Check if show product rating
     *
     * @return bool
     */
    public function isShowRating()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/show_rating');
    }

    /**
     * Check is show product description
     *
     * @return bool
     */
    public function isShowShortDescription()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/show_description');
    }

    /**
     * Check if show product sku
     *
     * @return bool
     */
    public function isShowSku()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/show_sku');
    }

    /**
     * Check if show add to cart button
     *
     * @return bool
     */
    public function isShowCartButton()
    {
        return (bool)$this->scopeConfig->getValue('searchautocomplete/general/product/show_cart');
    }

    /**
     * Check if optimize mobile
     *
     * @return bool
     */
    public function isOptimizeMobile()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/optimize_mobile');
    }

    /**
     * Get product description length
     *
     * @return int
     */
    public function getShortDescriptionLen()
    {
        return 100;
    }

    /**
     * Check if show product price
     *
     * @return bool
     */
    public function isShowPrice()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/product/show_price');
    }

    /**
     * Get delay before start search (in miliseconds)
     *
     * @return int
     */
    public function getDelay()
    {
        return intval($this->scopeConfig->getValue('searchautocomplete/general/delay'));
    }

    /**
     * Check if fast mode enabled
     *
     * @return bool
     */
    public function isFastMode()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/fast_mode');
    }

    /**
     * Get minimum number of chars to start search
     *
     * @return int
     */
    public function getMinChars()
    {
        return intval($this->scopeConfig->getValue('searchautocomplete/general/min_chars'));
    }

    /**
     * Get additional (custom) css styles
     *
     * @return string
     */
    public function getCssStyles()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/appearance/css');
    }

    /**
     * Get search index option value
     *
     * @param string $code
     * @param string $option
     * @return bool|string
     */
    public function getIndexOptionValue($code, $option)
    {
        $options = $this->getIndexOptions($code);

        if (isset($options[$option])) {
            return $options[$option];
        }

        return false;
    }

    /**
     * Get index options
     *
     * @param string $code
     * @return array
     */
    public function getIndexOptions($code)
    {
        if (isset($this->getIndicesConfig()[$code])) {
            return $this->getIndicesConfig()[$code];
        }

        return [];
    }

    /**
     * Get search indexes configuration
     *
     * @return array
     */
    public function getIndicesConfig()
    {
        if (CompatibilityService::is22() || CompatibilityService::is23()) {
            $indexes = \Zend_Json::decode($this->scopeConfig->getValue('searchautocomplete/general/index'));
        } else {
            $indexes = @unserialize($this->scopeConfig->getValue('searchautocomplete/general/index'));
        }

        return $indexes;
    }

    /**
     * Check if show popular searches
     *
     * @return bool
     */
    public function isShowPopularSearches()
    {
        return $this->scopeConfig->getValue('searchautocomplete/popular/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get popular searches
     *
     * @return array
     */
    public function getPopularSearches()
    {
        $result = $this->getDefaultPopularSearches();

        if (!count($result)) {
            $ignored = $this->getIgnoredSearches();

            $collection = $this->queryCollectionFactory->create()
                ->setPopularQueryFilter()
                ->setPageSize(20);

            /** @var \Magento\Search\Model\Query $query */
            foreach ($collection as $query) {
                $text = $query->getQueryText();
                if (!$text) {
                    //old magento 2
                    $text = $query->getName();
                }
                $isIgnored = false;
                foreach ($ignored as $word) {
                    if (strpos(strtolower($text), $word) !== false) {
                        $isIgnored = true;
                        break;
                    }
                }

                if (!$isIgnored) {
                    $result[] = mb_strtolower($text);
                }
            }
        }

        $result = array_slice(array_unique($result), 0, $this->getPopularLimit());
        $result = array_map('ucfirst', $result);

        return $result;
    }

    /**
     * Get default popular searches
     *
     * @return array
     */
    public function getDefaultPopularSearches()
    {
        $result = $this->scopeConfig->getValue('searchautocomplete/popular/default', ScopeInterface::SCOPE_STORE);
        $result = array_filter(array_map('trim', explode(',', $result)));

        return $result;
    }

    /**
     * Get ignored searches
     *
     * @return array
     */
    public function getIgnoredSearches()
    {
        $result = $this->scopeConfig->getValue('searchautocomplete/popular/ignored', ScopeInterface::SCOPE_STORE);
        $result = array_filter(array_map('strtolower', array_map('trim', explode(',', $result))));

        return $result;
    }

    /**
     * Get popular search limit
     *
     * @return int
     */
    public function getPopularLimit()
    {
        return intval($this->scopeConfig->getValue('searchautocomplete/popular/limit', ScopeInterface::SCOPE_STORE));
    }

    /**
     * Check if type ahead enabled
     *
     * @return bool
     */
    public function isTypeAheadEnabled()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/type_ahead');
    }

    /**
     * Check if out of stock products allowed in search
     *
     * @retun bool
     */
    public function isOutOfStockAllowed()
    {
        return $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock');
    }

    /**
     * Get autocomplete search layout
     *
     * @retun bool
     */
    public function getAutocompleteLayout()
    {
        return $this->scopeConfig->getValue('searchautocomplete/general/appearance/layout');
    }
}
