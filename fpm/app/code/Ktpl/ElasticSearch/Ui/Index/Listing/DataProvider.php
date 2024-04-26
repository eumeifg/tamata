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

namespace Ktpl\ElasticSearch\Ui\Index\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Ktpl\ElasticSearch\Api\Data\IndexInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\Index\Listing
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Retrieve search result output
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

        /** @var IndexInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = $item->getData();

            if ($item->getIsActive()) {
                $data[IndexInterface::STATUS] = $item->getStatus() == IndexInterface::STATUS_READY
                    ? __('Ready')
                    : __('Reindex Required');
            } else {
                $data[IndexInterface::STATUS] = __('Disabled');
            }

            $result['items'][] = $data;
        }

        return $result;
    }
}
