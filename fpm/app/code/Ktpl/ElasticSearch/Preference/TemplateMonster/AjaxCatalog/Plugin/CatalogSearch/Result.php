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

namespace Ktpl\ElasticSearch\Preference\TemplateMonster\AjaxCatalog\Plugin\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Result
 *
 * @package Ktpl\ElasticSearch\Preference\TemplateMonster\AjaxCatalog\Plugin\CatalogSearch
 */
class Result
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * Result constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param Resolver $layerResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Resolver $layerResolver
    )
    {
        $this->objectManager = $objectManager;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Get ajax search result
     *
     * @param $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $helper = $this->objectManager
            ->get('TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse');

        $request = $subject->getRequest();
        if ($request->isXmlHttpRequest()) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

            return $helper->getAjaxSearchResult($subject, $proceed);
        } else {
            $returnValue = $proceed();

            return $returnValue;
        }
    }
}
