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

namespace Ktpl\ElasticSearch\Block\Adminhtml\Config\Form\Field;

/**
 * Class LongTailExpressions
 * @package Ktpl\ElasticSearch\Block\Adminhtml\Config\Form\Field
 */
class LongTailExpressions extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->addColumn('match_expr', ['label' => __('Match Expression')]);
        $this->addColumn('replace_expr', ['label' => __('Replace Expression')]);
        $this->addColumn('replace_char', ['label' => __('Replace Char')]);

        parent::_construct();
    }
}
