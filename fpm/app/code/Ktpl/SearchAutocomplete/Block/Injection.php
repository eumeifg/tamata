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

namespace Ktpl\SearchAutocomplete\Block;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Helper\Data as SearchHelper;
use Ktpl\SearchAutocomplete\Model\Config;

/**
 * Class Injection
 *
 * @package Ktpl\SearchAutocomplete\Block
 */
class Injection extends Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var SearchHelper
     */
    protected $searchHelper;

    /**
     * Injection constructor.
     * @param Context $context
     * @param Config $config
     * @param FormatInterface $localeFormat
     * @param SearchHelper $searchHelper
     */
    public function __construct(
        Context $context,
        Config $config,
        FormatInterface $localeFormat,
        SearchHelper $searchHelper
    )
    {
        $this->storeManager = $context->getStoreManager();
        $this->config = $config;
        $this->localeFormat = $localeFormat;
        $this->searchHelper = $searchHelper;

        parent::__construct($context);
    }

    /**
     * Get js config data
     *
     * @return array
     */
    public function getJsConfig()
    {
        return [
            'query' => $this->searchHelper->getEscapedQueryText(),
            'priceFormat' => $this->localeFormat->getPriceFormat(),
            'minSearchLength' => $this->config->getMinChars(),
            'url' => $this->getUrl(
                'searchautocomplete/ajax/suggest',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
            'storeId' => $this->storeManager->getStore()->getId(),
            'delay' => $this->config->getDelay(),
            'layout' => $this->config->getAutocompleteLayout(),
            'popularTitle' => __('Hot Searches')->render(),
            'popularSearches' => $this->config->isShowPopularSearches() ? $this->config->getPopularSearches() : [],
            'isTypeaheadEnabled' => $this->config->isTypeAheadEnabled(),
            'typeaheadUrl' => $this->getUrl(
                'searchautocomplete/ajax/typeahead',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
        ];
    }

    /**
     * Get css styles
     *
     * @return string
     */
    public function getCssStyles()
    {
        return $this->config->getCssStyles();
    }
}
