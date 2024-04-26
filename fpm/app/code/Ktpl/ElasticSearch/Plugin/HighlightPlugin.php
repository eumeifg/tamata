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

use Magento\Search\Model\QueryFactory;
use Ktpl\ElasticSearch\Block\Result;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class HighlightPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class HighlightPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * HighlightPlugin constructor.
     *
     * @param Config $config
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        Config $config,
        QueryFactory $queryFactory
    )
    {
        $this->config = $config;
        $this->queryFactory = $queryFactory;
    }

    /**
     * Get query text html
     *
     * @param Result $block
     * @param $html
     * @return string
     */
    public function afterToHtml(
        Result $block,
        $html
    )
    {
        if (!$this->config->isHighlightingEnabled()) {
            return $html;
        }

        $html = $this->highlight(
            $html,
            $this->queryFactory->get()->getQueryText()
        );

        return $html;
    }

    /**
     * Highlight html
     *
     * @param string $html
     * @param string $query
     * @return string
     */
    public function highlight($html, $query)
    {
        if (strlen($query) < 3) {
            return $html;
        }

        $query = $this->removeSpecialChars($query);
        preg_match_all("/[\$\/\|\-\w\d\s]*" . $query . "[\$\/\|\-\w\d\s]*<\s*\/\s*a/is", $html, $matches);

        foreach ($matches[0] as $match) {
            $html = $this->_highlight($html, $match, $query);
        }

        return $html;
    }

    /**
     * Remove special characters
     *
     * @param string $query
     * @return string
     */
    public function removeSpecialChars($query)
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = ' ';

        return preg_replace($pattern, $replace, $query);
    }

    /**
     * Highlight html
     *
     * @param string $html
     * @param string $match
     * @param string $query
     * @return string
     */
    private function _highlight($html, $match, $query)
    {
        $replacement = substr_replace($match, '<span class="ktpl-search__highlight">', stripos($match, $query), 0);
        $replacement = substr_replace($replacement, '</span>', stripos($replacement, $query) + strlen($query), 0);

        return str_replace($match, $replacement, $html);
    }
}
