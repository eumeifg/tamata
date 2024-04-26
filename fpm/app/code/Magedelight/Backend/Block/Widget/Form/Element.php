<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Backend
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Backend\Block\Widget\Form;

use Magento\Framework\Data\Form;

/**
 * Form element widget block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Element extends \Magedelight\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_element;

    /**
     * @var Form
     */
    protected $_form;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_formBlock;

    /**
     * @var string
     */
    protected $_template = 'Magedelight_Backend::widget/form/element.phtml';

    /**
     * @param string $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->_element = $element;
        return $this;
    }

    /**
     * @param Form $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $formBlock
     * @return $this
     */
    public function setFormBlock($formBlock)
    {
        $this->_formBlock = $formBlock;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->assign('form', $this->_form);
        $this->assign('element', $this->_element);
        $this->assign('formBlock', $this->_formBlock);

        return parent::_beforeToHtml();
    }
}
