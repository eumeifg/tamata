<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block;

use Amasty\Xsearch\Model\Config;
use Magento\Framework\View\Element\Template;

class Jsinit extends Template
{
    /**
     * @var \Amasty\Xsearch\Block\Search\Recent
     */
    private $recentSearch;

    /**
     * @var \Amasty\Xsearch\Block\Search\Popular
     */
    private $popularSearch;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Config
     */
    private $moduleConfigProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Config $moduleConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlHelper = $urlHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->moduleConfigProvider = $moduleConfigProvider;
    }

    /**
     * @param null $url
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptions($url = null)
    {
        return $this->jsonEncoder->encode([
            'url' => $this->getUrl('amasty_xsearch/autocomplete/index'),
            'isDynamicWidth' => $this->moduleConfigProvider->isDynamicWidth(),
            'width' => $this->moduleConfigProvider->getPopupWidth(),
            'minChars' => $this->moduleConfigProvider->getMinChars(),
            'currentUrlEncoded' => $this->getCurrentUrlEncoded($url)
        ]);
    }

    /**
     * @param null $url
     * @return string
     */
    public function getCurrentUrlEncoded($url = null)
    {
        return $this->urlHelper->getEncodedUrl($url);
    }

    /**
     * @return bool
     */
    public function isShowRecentPreload()
    {
        return $this->moduleConfigProvider->isRecentSearchesEnabled()
            && $this->moduleConfigProvider->getShowRecentByFirstClick()
            && count($this->getRecentSearch()->getResults());
    }

    /**
     * @return bool
     */
    public function isShowPopularPreload()
    {
        return $this->moduleConfigProvider->isPopularSearchesEnabled()
            && $this->moduleConfigProvider->getShowPopularByFirstClick()
            && count($this->getPopularSearch()->getResults());
    }

    /**
     * @return string
     */
    public function getPreload()
    {
        $recentHtml = '';
        $popularHtml = '';
        if ($this->isShowRecentPreload()) {
            $recentHtml .= $this->getRecentSearch()->toHtml();
        }

        if ($this->isShowPopularPreload()) {
            $popularHtml .= $this->getPopularSearch()->toHtml();
        }

        $recentPos = $this->moduleConfigProvider->getRecentSearchesPosition();
        $popularPos = $this->moduleConfigProvider->getPopularSearchesPosition();

        if ($recentPos < $popularPos) {
            return $recentHtml . $popularHtml;
        }

        return $popularHtml . $recentHtml;
    }

    /**
     * @return \Amasty\Xsearch\Block\Search\Recent
     */
    private function getRecentSearch()
    {
        if (!$this->recentSearch) {
            $this->recentSearch = $this->_layout
                ->createBlock(\Amasty\Xsearch\Block\Search\Recent::class);
        }

        return $this->recentSearch;
    }

    /**
     * @return \Amasty\Xsearch\Block\Search\Popular
     */
    private function getPopularSearch()
    {
        if (!$this->popularSearch) {
            $this->popularSearch = $this->_layout
                ->createBlock(\Amasty\Xsearch\Block\Search\Popular::class);
        }

        return $this->popularSearch;
    }
}
