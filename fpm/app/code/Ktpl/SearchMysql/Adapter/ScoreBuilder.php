<?php
/**
 * KrishTechnolabs
 *
 * PHP version 7
 *
 * @category  KrishTechnolabs
 * @package   Ktpl_SearchMysql
 * @author    Dhara Bhatti <dhara.bhatti@krishtechnolabs.com>
 * @copyright 2019 (c) KrishTechnolabs (https://www.KrishTechnolabs.com/)
 * @license   https://www.krishtechnolabs.com/LICENSE.txt Krish License
 * @link      https://www.krishtechnolabs.com/
 */

namespace Ktpl\SearchMysql\Adapter;

/**
 * Class ScoreBuilder
 *
 * @package Ktpl\SearchMysql\Adapter
 */
class ScoreBuilder extends \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder
{
    /**
     * @var string
     */
    const WEIGHT_FIELD = 'search_weight';
    /**
     * @var string
     */
    private $scoreCondition = '';

    /**
     * Get generated sql condition for global score
     *
     * @return string
     */
    public function build()
    {
        $scoreCondition = $this->scoreCondition;
        $this->clear();
        $scoreAlias = $this->getScoreAlias();

        return new \Zend_Db_Expr("({$scoreCondition}) AS {$scoreAlias}");
    }

    /**
     * Clear score manager
     *
     * @return void
     */
    private function clear()
    {
        $this->scoreCondition = '';
    }

    /**
     * Get column alias for global score query in sql
     *
     * @return string
     */
    public function getScoreAlias()
    {
        return 'score';
    }

    /**
     * Start Query
     *
     * @return void
     */
    public function startQuery()
    {
        $this->addPlus();
        $this->scoreCondition .= '(';
    }

    /**
     * Add Plus sign for Score calculation
     *
     * @return void
     */
    private function addPlus()
    {
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) !== '(') {
            $this->scoreCondition .= ' + ';
        }
    }

    /**
     * End Query
     *
     * @param float $boost
     * @return void
     */
    public function endQuery($boost)
    {
        if (!empty($this->scoreCondition) && substr($this->scoreCondition, -1) !== '(') {
            $this->scoreCondition .= ") * {$boost}";
        } else {
            $this->scoreCondition .= '0)';
        }
    }

    /**
     * Add Condition for score calculation
     *
     * @param string $score
     * @param bool $useWeights
     * @return void
     */
    public function addCondition($score, $useWeights = true)
    {
        $this->addPlus();
        $condition = "{$score}";

        $weight = 'cea.' . self::WEIGHT_FIELD;

        if ($useWeights) {
            $condition = "SUM({$condition} * POW(2, $weight))";
        }

        $this->scoreCondition .= $condition;
    }
}
