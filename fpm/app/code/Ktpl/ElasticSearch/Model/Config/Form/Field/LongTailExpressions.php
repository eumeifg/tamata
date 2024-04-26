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

namespace Ktpl\ElasticSearch\Model\Config\Form\Field;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Class LongTailExpressions
 *
 * @package Ktpl\ElasticSearch\Model\Config\Form\Field
 */
class LongTailExpressions extends ArraySerialized
{
    /**
     * {@inheritdoc}
     *
     * @return ArraySerialized
     * @throws \Exception
     */
    public function beforeSave()
    {
        /** @var array $expressions */
        $expressions = $this->getValue();

        if (!is_array($expressions)) {
            $expressions = [];
        }

        foreach ($expressions as $rowKey => $row) {
            if ($rowKey === '__empty') {
                continue;
            }

            foreach (['match_expr', 'replace_expr'] as $fieldName) {
                if (!isset($row[$fieldName])) {
                    throw new \Exception(
                        __('Expression does not contain field \'%1\'', $fieldName)
                    );
                }
            }

            $expressions[$rowKey]['match_expr'] = $this->composeRegexp($row['match_expr']);
            $expressions[$rowKey]['replace_expr'] = $this->composeRegexp($row['replace_expr']);
        }

        $this->setValue($expressions);

        return parent::beforeSave();
    }

    /**
     * Prepare regular expression
     *
     * @param string $search
     * @return string
     * @throws \Exception
     */
    protected function composeRegexp($search)
    {
        // If valid regexp entered - do nothing
        if (@preg_match($search, '') !== false) {
            return $search;
        }

        // Find out - whether user wanted to enter regexp or normal string.
        if ($this->isRegexp($search)) {
            throw new \Exception(__('Invalid regular expression: "%1".', $search));
        }

        return '/' . preg_quote($search, '/') . '/i';
    }

    /**
     * Check if regular expression exists
     *
     * @param string $search
     * @return bool
     */
    protected function isRegexp($search)
    {
        if (strlen($search) < 3) {
            return false;
        }

        $possibleDelimiters = '/#~%';
        // Limit delimiters to reduce possibility, that we miss string with regexp.

        // Starts with a delimiter
        if (strpos($possibleDelimiters, $search[0]) !== false) {
            return true;
        }

        // Ends with a delimiter and (possible) modifiers
        $pattern = '/[' . preg_quote($possibleDelimiters, '/') . '][imsxeADSUXJu]*$/';
        if (preg_match($pattern, $search)) {
            return true;
        }

        return false;
    }
}
