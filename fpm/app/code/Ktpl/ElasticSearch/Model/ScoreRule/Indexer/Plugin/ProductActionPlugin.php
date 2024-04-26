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

namespace Ktpl\ElasticSearch\Model\ScoreRule\Indexer\Plugin;

/**
 * Class ProductActionPlugin
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Indexer\Plugin
 */
class ProductActionPlugin extends AbstractPlugin
{
    /**
     * Reindex on product attribute mass change
     *
     * @param \Magento\Catalog\Model\Product\Action $subject
     * @param \Closure $closure
     * @param array $productIds
     * @param array $attrData
     * @param $storeId
     * @return mixed
     */
    public function aroundUpdateAttributes(
        \Magento\Catalog\Model\Product\Action $subject,
        \Closure $closure,
        array $productIds,
        array $attrData,
        $storeId
    )
    {
        $result = $closure($productIds, $attrData, $storeId);
        $this->reindexList(array_unique($productIds));
        return $result;
    }

    /**
     * Reindex on product websites mass change
     *
     * @param \Magento\Catalog\Model\Product\Action $subject
     * @param \Closure $closure
     * @param array $productIds
     * @param array $websiteIds
     * @param $type
     * @return mixed
     */
    public function aroundUpdateWebsites(
        \Magento\Catalog\Model\Product\Action $subject,
        \Closure $closure,
        array $productIds,
        array $websiteIds,
        $type
    )
    {
        $result = $closure($productIds, $websiteIds, $type);
        $this->reindexList(array_unique($productIds));
        return $result;
    }
}
