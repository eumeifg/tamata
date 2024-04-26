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

namespace Ktpl\ElasticSearch\Ui\Stopword\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Ktpl\ElasticSearch\Api\Data\StopwordInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Stopword\Listing
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

        /** @var StopwordInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                StopwordInterface::ID => $item->getId(),
                StopwordInterface::TERM => $item->getTerm(),
                StopwordInterface::STORE_ID => $item->getStoreId(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
