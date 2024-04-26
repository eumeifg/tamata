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

namespace Ktpl\SearchAutocomplete\Index\Magento\Search;

use Ktpl\SearchAutocomplete\Index\AbstractIndex;
use Magento\Framework\UrlFactory;

/**
 * Class Query
 *
 * @package Ktpl\SearchAutocomplete\Index\Magento\Search
 */
class Query extends AbstractIndex
{
    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * Query constructor.
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

        /** @var \Magento\Search\Model\Query $query */
        foreach ($this->getCollection() as $query) {
            $url = $this->urlFactory->create();
            $url->setQueryParam('q', $query->getQueryText());
            $url = $url->getUrl('catalogsearch/result');

            $key = strtolower(trim($query->getQueryText()));

            $items[$key] = [
                'query_text' => $query->getQueryText(),
                'num_results' => $query->getNumResults(),
                'popularity' => $query->getPopularity(),
                'url' => $url,
            ];
        }

        return array_values($items);
    }
}
