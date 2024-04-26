<?php
/*
 * Copyright © 2018 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\NavLinks\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class AdditionalEmail
 */
class MenuItem extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('sortorder', ['label' => __('ID/Order'), 'class' => 'required-entry', 'size' => '10px']);
        $this->addColumn('name', ['label' => __('Menu Title'), 'class' => 'required-entry']);
        $this->addColumn('url', ['label' => __('Url'), 'class' => 'required-entry', 'size' => '50px']);
        $this->addColumn('icon_class', ['label' => __('Class')]);
        $this->addColumn('parent',['label' => __('Parent'), 'size' => '10px']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Menu Item');
    }
}
