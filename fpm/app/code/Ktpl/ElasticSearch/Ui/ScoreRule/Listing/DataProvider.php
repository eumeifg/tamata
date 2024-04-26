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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Listing
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

        /** @var ScoreRuleInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $title = $item->getTitle();
            list($sign, $number) = explode('|', $item->getScoreFactor());
            $class = $sign == '*' || $sign == '+' ? 'plus' : 'minus';
            if ($sign == '*') {
                $sign = '×';
            } elseif ($sign == '/') {
                $sign = '÷';
            }

            $title .= "<div class='$class'><span>$sign</span><i>$number</i></div>";

            $conditions = [];
            $getConditions = strpos($item->getRule()->getConditions()->asStringRecursive(), PHP_EOL);
            $getPostConditions = strpos($item->getRule()->getPostConditions()->asStringRecursive(), PHP_EOL);

            if ($getConditions !== false && $getConditions > 0) {
                $conditions[] = $item->getRule()->getConditions()->asStringRecursive();
            }

            if ($getPostConditions !== false && $getPostConditions > 0) {
                $conditions[] = $item->getRule()->getPostConditions()->asStringRecursive();
            }

            $data = [
                ScoreRuleInterface::ID => $item->getId(),
                ScoreRuleInterface::TITLE => $title,
                ScoreRuleInterface::IS_ACTIVE => $item->isActive(),
                ScoreRuleInterface::ACTIVE_FROM => $item->getActiveFrom(),
                ScoreRuleInterface::ACTIVE_TO => $item->getActiveTo(),
                ScoreRuleInterface::STORE_IDS => implode(',', $item->getStoreIds()),
                'conditions' => implode(PHP_EOL . PHP_EOL, $conditions),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
