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

namespace Ktpl\SearchMysql\Adapter\Query\Builder;

use Magento\Framework\DB\Helper\Mysql\Fulltext;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\Search\Adapter\Mysql\Field\ResolverInterface;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\DB\Helper as DbHelper;
use Ktpl\ElasticSearch\Api\Service\QueryServiceInterface;
use Ktpl\ElasticSearch\Model\Config;

/**
 * Class Match
 *
 * @package Ktpl\SearchMysql\Adapter\Query\Builder
 */
class Match extends \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
{
    /**
     * Allowed special characters
     */
    const SPECIAL_CHARACTERS = '-+~/\\<>\'":*$#@()!,.?`=';

    /**
     * Allowed minimal character length
     */
    const MINIMAL_CHARACTER_LENGTH = 3;
    /**
     * @var DbHelper
     */
    protected $dbHelper;
    /**
     * @var string[]
     */
    private $replaceSymbols = [];
    /**
     * @var ResolverInterface
     */
    private $resolver;
    /**
     * @var Fulltext
     */
    private $fulltextHelper;
    /**
     * @var string
     */
    private $fulltextSearchMode;

    /**
     * @var QueryServiceInterface
     */
    private $queryService;

    /**
     * Match constructor.
     *
     * @param ResolverInterface $resolver
     * @param Fulltext $fulltextHelper
     * @param DbHelper $dbHelper
     * @param QueryServiceInterface $queryService
     * @param string $fulltextSearchMode
     */
    public function __construct(
        ResolverInterface $resolver,
        Fulltext $fulltextHelper,
        DbHelper $dbHelper,
        QueryServiceInterface $queryService,
        $fulltextSearchMode = Fulltext::FULLTEXT_MODE_BOOLEAN
    )
    {
        $this->resolver = $resolver;
        $this->replaceSymbols = str_split(self::SPECIAL_CHARACTERS, 1);
        $this->fulltextHelper = $fulltextHelper;
        $this->queryService = $queryService;
        $this->dbHelper = $dbHelper;
        $this->fulltextSearchMode = $fulltextSearchMode;

        parent::__construct($resolver, $fulltextHelper, $fulltextSearchMode);
    }

    /**
     * Build search
     *
     * @param ScoreBuilder $scoreBuilder
     * @param Select $select
     * @param RequestQueryInterface $query
     * @param $conditionType
     * @return Select
     */
    public function build(
        ScoreBuilder $scoreBuilder,
        Select $select,
        RequestQueryInterface $query,
        $conditionType
    )
    {
        /** @var $query \Magento\Framework\Search\Request\Query\Match */

        $fieldList = [];
        foreach ($query->getMatches() as $match) {
            $fieldList[] = $match['field'];
        }

        $resolvedFieldList = $this->resolver->resolve($fieldList);

        $fieldIds = [];
        $columns = [];
        foreach ($resolvedFieldList as $field) {
            if ($field->getType() === FieldInterface::TYPE_FULLTEXT && $field->getAttributeId()) {
                $fieldIds[] = $field->getAttributeId();
            }
            $column = $field->getColumn();
            $columns[$column] = $column;
        }

        $searchQuery = $this->queryService->build($query->getValue());

        $exactMatchQuery = $this->compileQuery($columns, $searchQuery);

        $scoreQuery = $this->getScoreQuery($columns, $query->getValue());

        $scoreBuilder->addCondition(new \Zend_Db_Expr($scoreQuery), true);

        if ($fieldIds) {
            $select->where(sprintf('search_index.attribute_id IN (%s)', implode(',', $fieldIds)));
        }

        if ($exactMatchQuery) {
            $select->having(new \Zend_Db_Expr($exactMatchQuery));
        }

        $select->group('entity_id');

        return $select;
    }

    /**
     * Compile query
     *
     * @param array $columns
     * @param array $query
     * @param bool $isNot
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function compileQuery($columns, $query, $isNot = false)
    {
        $compiled = [];
        foreach ($query as $directive => $value) {
            switch ($directive) {
                case '$like':
                    $compiled[] = '(' . $this->compileQuery($columns, $value, $isNot) . ')';
                    break;

                case '$!like':
                    $compiled[] = '(' . $this->compileQuery($columns, $value, true) . ')';
                    break;

                case '$and':
                    $and = [];
                    foreach ($value as $item) {
                        $and[] = $this->compileQuery($columns, $item, $isNot);
                    }
                    $compiled[] = '(' . implode(' and ', $and) . ')';
                    break;

                case '$or':
                    $or = [];
                    foreach ($value as $item) {
                        $or[] = $this->compileQuery($columns, $item, $isNot);
                    }
                    $compiled[] = '(' . implode(' or ', $or) . ')';
                    break;

                case '$term':
                    $phrase = $value['$phrase'];

                    switch ($value['$wildcard']) {
                        case Config::WILDCARD_PREFIX:
                            $phrase = "$phrase ";
                            break;
                        case Config::WILDCARD_SUFFIX:
                            $phrase = " $phrase";
                            break;
                        case Config::WILDCARD_DISABLED:
                            $phrase = " $phrase ";
                            break;
                    }

                    $likes = [];
                    foreach ($columns as $attribute) {
                        $attribute = new \Zend_Db_Expr('GROUP_CONCAT(' . $attribute . ')');
                        $options = ['position' => 'any'];
                        if ($isNot) {
                            $likes[] = new \Zend_Db_Expr(
                                $attribute . ' NOT LIKE ' . $this->dbHelper->addLikeEscape($phrase, $options)
                            );
                        } else {
                            $likes[] = $this->dbHelper->getCILike($attribute, $phrase, $options);
                        }
                    }

                    $compiled[] = implode(' or ', $likes);

                    break;
            }
        }

        return implode(' AND ', $compiled);
    }

    /**
     * Get score query
     *
     * @param array $columns
     * @param string $query
     * @return string
     */
    public function getScoreQuery($columns, $query)
    {
        $cases = [];
        $fullCases = [];

        $words = preg_split('#\s#siu', $query, null, PREG_SPLIT_NO_EMPTY);

        foreach ($columns as $column) {
            $cases[5][] = $this->dbHelper->getCILike($column, ' ' . $query . ' ');
        }

        foreach ($words as $word) {
            foreach ($columns as $column) {
                $cases[3][] = $this->dbHelper->getCILike($column, ' ' . $word . ' ', ['position' => 'any']);
                $cases[2][] = $this->dbHelper->getCILike($column, $word, ['position' => 'any']);
            }
        }

        foreach ($words as $word) {
            foreach ($columns as $column) {
                $e = strlen($word) . ' * (';
                $e .= '(' . strlen($word) . ' / LENGTH(' . $column . ')) + ';
                $e .= '(1/(LENGTH(' . $column . ') - ( LENGTH(' . $column . ') 
                    - LOCATE("' . addslashes($word) . '",' . $column . ')))))';
                $locate = new \Zend_Db_Expr($e);

                $cases[$locate->__toString()][] = $locate;
            }
        }

        foreach ($cases as $weight => $conditions) {
            foreach ($conditions as $condition) {
                $fullCases[] = 'CASE WHEN ' . $condition . ' THEN ' . $weight . ' ELSE 0 END';
            }
        }

        if (count($fullCases)) {
            $select = '(' . implode('+', $fullCases) . ')';
        } else {
            $select = '0';
        }

        $select = 'CASE WHEN cea.search_weight > 1 THEN(' . $select . ') ELSE 0 END';

        return $select;
    }

    /**
     * Prepare fast query
     *
     * @param string $queryValue
     * @param string $conditionType
     * @return string
     */
    protected function prepareFastQuery($queryValue, $conditionType)
    {
        $queryValue = str_replace($this->replaceSymbols, ' ', $queryValue);

        $stringPrefix = '';
        if ($conditionType === BoolExpression::QUERY_CONDITION_MUST) {
            $stringPrefix = '+';
        } elseif ($conditionType === BoolExpression::QUERY_CONDITION_NOT) {
            $stringPrefix = '-';
        }

        $queryValues = explode(' ', $queryValue);

        foreach ($queryValues as $queryKey => $queryValue) {
            if (empty($queryValue)) {
                unset($queryValues[$queryKey]);
            } else {
                $stringSuffix = '*';
                $queryValues[$queryKey] = $stringPrefix . $queryValue . $stringSuffix;
            }
        }

        $queryValue = implode(' ', $queryValues);

        return $queryValue;
    }
}
