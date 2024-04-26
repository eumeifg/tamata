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

namespace Ktpl\SearchAutocomplete\Index\Magento\Catalog;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\UrlFactory;

/**
 * Class Attribute
 *
 * @package Ktpl\SearchAutocomplete\Index\Magento\Catalog
 */
class Attribute extends AbstractIndex
{
    /**
     * @var UrlFactory
     */
    private $urlFactory;

    /**
     * Attribute constructor.
     *
     * @param UrlFactory $urlFactory
     */
    public function __construct(
        UrlFactory $urlFactory
    )
    {
        $this->urlFactory = $urlFactory;
    }

    /**
     * Get items
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        $attr = $this->index->getData('properties/attribute');

        foreach ($this->getCollection() as $value) {
            $url = $this->urlFactory->create()
                ->getUrl('catalogsearch/advanced/result', ['_query' => [$attr => $value->getValue()]]);

            $items[] = [
                'name' => $value->getLabel(),
                'url' => $url,
            ];
        }

        return $items;
    }
}
