<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model;

use Magento\Store\Model\ScopeInterface;
use Amasty\Xsearch\Model\System\Config\Source\RelatedTerms;

class Config
{
    const MODULE_SECTION_NAME = 'amasty_xsearch/';
    const PERMANENT_REDIRECT_CODE = 301;
    const TEMPORARY_REDIRECT_CODE = 302;
    const DEFAULT_POPUP_WIDTH = 900;

    const XML_PATH_TEMPLATE_WIDTH = 'general/popup_width';
    const XML_PATH_TEMPLATE_MIN_CHARS = 'general/min_chars';
    const XML_PATH_TEMPLATE_DYNAMIC_WIDTH = 'general/dynamic_search_width';
    const XML_PATH_RECENT_SEARCHES_FIRST_CLICK = 'recent_searches/first_click';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED = 'recent_searches/enabled';
    const XML_PATH_POPULAR_SEARCHES_FIRST_CLICK = 'popular_searches/first_click';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED = 'popular_searches/enabled';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION = 'recent_searches/position';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION = 'popular_searches/position';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_SECTION_NAME . $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return int
     */
    public function getRedirectType()
    {
        return $this->getModuleConfig('general/four_zero_four_redirect');
    }

    /**
     * @return bool
     */
    public function hasRedirect()
    {
        return (bool)$this->getRedirectType();
    }

    /**
     * @return bool
     */
    public function isPermanentRedirect()
    {
        return $this->getRedirectType() == self::PERMANENT_REDIRECT_CODE;
    }

    /**
     * @return int
     */
    public function getRedirectCode()
    {
        return $this->isPermanentRedirect() ? self::PERMANENT_REDIRECT_CODE : self::TEMPORARY_REDIRECT_CODE;
    }

    /**
     * @param int $searchResultCount
     * @return bool
     */
    public function canShowRelatedTerms($searchResultCount = 0)
    {
        switch ($this->getModuleConfig('general/show_related_terms')) {
            case RelatedTerms::DISABLED:
                return false;
            case RelatedTerms::SHOW_ALWAYS:
                return true;
            case RelatedTerms::SHOW_ONLY_WITHOUT_RESULTS:
                return !$searchResultCount;
        }

        return false;
    }
    /**
     * @return bool
     */
    public function canShowRelatedNumberResults()
    {
        return (bool)$this->getModuleConfig('general/show_related_terms_results');
    }

    /**
     * @return bool
     */
    public function isShowOutOfStockLast()
    {
        return (bool)$this->getModuleConfig('product/out_of_stock_last');
    }

    public function getShowRecentByFirstClick(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_RECENT_SEARCHES_FIRST_CLICK);
    }

    public function getShowPopularByFirstClick(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_POPULAR_SEARCHES_FIRST_CLICK);
    }

    public function isDynamicWidth(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_DYNAMIC_WIDTH);
    }

    public function getPopupWidth(): int
    {
        return (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_WIDTH) ?: self::DEFAULT_POPUP_WIDTH;
    }

    public function getMinChars(): int
    {
        $minChars = (int)$this->getModuleConfig(self::XML_PATH_TEMPLATE_MIN_CHARS);

        return max(1, $minChars);
    }

    public function getPosition(string $ConfigPath): int
    {
        $position = (int)$this->getModuleConfig($ConfigPath);

        return max(1, $position);
    }

    public function getRecentSearchesPosition(): int
    {
        return $this->getPosition(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION);
    }

    public function getPopularSearchesPosition(): int
    {
        return $this->getPosition(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION);
    }

    public function isPopularSearchesEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED);
    }

    public function isRecentSearchesEnabled(): bool
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED);
    }
}
