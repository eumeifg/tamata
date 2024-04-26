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

use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ProductPlugin
 *
 * @package Ktpl\ElasticSearch\Model\ScoreRule\Indexer\Plugin
 */
class ProductPlugin extends AbstractPlugin
{
    /**
     * Reindex on product save
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundSave(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }

    /**
     * Add commit callback
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return mixed
     * @throws \Exception
     */
    private function addCommitCallback(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        try {
            $productResource->beginTransaction();
            $result = $proceed($product);
            $productResource->addCommitCallback(function () use ($product) {
                $this->reindexRow($product->getEntityId());
            });
            $productResource->commit();
        } catch (\Exception $e) {
            $productResource->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Reindex on product delete
     *
     * @param ResourceProduct $productResource
     * @param \Closure $proceed
     * @param AbstractModel $product
     * @return ResourceProduct
     * @throws \Exception
     */
    public function aroundDelete(ResourceProduct $productResource, \Closure $proceed, AbstractModel $product)
    {
        return $this->addCommitCallback($productResource, $proceed, $product);
    }
}
