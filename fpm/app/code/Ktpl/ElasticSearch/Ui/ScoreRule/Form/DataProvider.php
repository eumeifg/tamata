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

namespace Ktpl\ElasticSearch\Ui\ScoreRule\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Ktpl\ElasticSearch\Api\Data\ScoreRuleInterface;
use Ktpl\ElasticSearch\Api\Repository\ScoreRuleRepositoryInterface;

/**
 * Class DataProvider
 *
 * @package Ktpl\ElasticSearch\Ui\ScoreRule\Form
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * DataProvider constructor.
     *
     * @param ScoreRuleRepositoryInterface $repository
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        ScoreRuleRepositoryInterface $repository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    )
    {
        $this->collection = $repository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $scoreRule) {
            list ($p, $factor, $factorRelatively) = explode('|', $scoreRule->getScoreFactor());

            $factorType = ($p == '*' || $p == '+') ? '+' : '-';
            $factorUnit = ($p == '*' || $p == '/') ? '*' : '+';

            $data = [
                ScoreRuleInterface::ID => $scoreRule->getId(),
                ScoreRuleInterface::TITLE => $scoreRule->getTitle(),
                ScoreRuleInterface::IS_ACTIVE => $scoreRule->isActive(),
                ScoreRuleInterface::ACTIVE_FROM => $scoreRule->getActiveFrom(),
                ScoreRuleInterface::ACTIVE_TO => $scoreRule->getActiveTo(),
                ScoreRuleInterface::STORE_IDS => $scoreRule->getStoreIds(),

                ScoreRuleInterface::SCORE_FACTOR => $factor,
                ScoreRuleInterface::SCORE_FACTOR . '_type' => $factorType,
                ScoreRuleInterface::SCORE_FACTOR . '_unit' => $factorUnit,
                ScoreRuleInterface::SCORE_FACTOR . '_relatively' => $factorRelatively,
            ];

            $result[$scoreRule->getId()] = $data;
        }

        return $result;
    }
}
