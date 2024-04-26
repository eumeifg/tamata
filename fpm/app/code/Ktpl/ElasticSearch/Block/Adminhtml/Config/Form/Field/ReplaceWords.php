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
 * Class ReplaceWords
 *
 * @package Ktpl\ElasticSearch\Block\Adminhtml\Config\Form\Field
 */
class ReplaceWords extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->addColumn('from', ['label' => __('Find word')]);
        $this->addColumn('to', ['label' => __('Replace with')]);

        parent::_construct();
    }
}
