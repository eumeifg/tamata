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
 * Class WildcardExceptions
 * @package Ktpl\ElasticSearch\Block\Adminhtml\Config\Form\Field
 */
class WildcardExceptions extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->addColumn('exception', [
            'label' => __('Word'),
        ]);

        parent::_construct();
    }
}
