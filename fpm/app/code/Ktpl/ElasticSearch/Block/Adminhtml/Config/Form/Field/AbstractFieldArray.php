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

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray as FormAbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class AbstractFieldArray
 *
 * @package Ktpl\ElasticSearch\Block\Adminhtml\Config\Form\Field
 */
class AbstractFieldArray extends FormAbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->setTemplate('config/form/field/array.phtml');

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }
    
    /**
     * {@inheritdoc}
     *
     * @param AbstractElement $element
     * @return string
     * @throws \Exception
     */
    public function render(AbstractElement $element)
    {
        $this->setElement($element);

        return $this->_toHtml();
    }
}
