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

namespace Ktpl\ElasticSearch\Ui\Synonym\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Ktpl\ElasticSearch\Api\Data\SynonymInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Synonym\Listing
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Output search result
     *
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items' => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var SynonymInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                SynonymInterface::ID => $item->getId(),
                SynonymInterface::TERM => $item->getTerm(),
                SynonymInterface::SYNONYMS => $item->getSynonyms(),
                SynonymInterface::STORE_ID => $item->getStoreId(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
