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

use Ktpl\ElasticSearch\Block\Result;
use Ktpl\ElasticSearch\Model\Config;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\App\ResponseInterface;

/**
 * Class SingleResultPlugin
 *
 * @package Ktpl\ElasticSearch\Plugin
 */
class SingleResultPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * SingleResultPlugin constructor.
     *
     * @param Config $config
     * @param LayerResolver $layerResolver
     * @param ResponseInterface $response
     */
    public function __construct(
        Config $config,
        LayerResolver $layerResolver,
        ResponseInterface $response
    )
    {
        $this->config = $config;
        $this->layerResolver = $layerResolver;
        $this->response = $response;
    }

    /**
     * Add layer resolver
     *
     * @param Result $block
     * @param $html
     * @return mixed
     */
    public function afterToHtml(
        Result $block,
        $html
    )
    {
        if (!$this->config->isRedirectOnSingleResult()) {
            return $html;
        }

        if ($this->layerResolver->get()->getProductCollection()->getSize() == 1) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->layerResolver->get()->getProductCollection()->getFirstItem();

            $this->response
                ->setRedirect($product->getProductUrl())
                ->setStatusCode(301)
                ->sendResponse();
        }

        return $html;
    }
}
