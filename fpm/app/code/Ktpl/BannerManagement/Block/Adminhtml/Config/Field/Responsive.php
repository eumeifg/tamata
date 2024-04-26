<?php
/*
 * Copyright © 2019 Krish Technolabs. All rights reserved.
 * See COPYING.txt for license details
 */

namespace Ktpl\BannerManagement\Block\Adminhtml\Config\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Responsive
 * @package Ktpl\BannerManagement\Block\Adminhtml\Config\Field
 */
class Responsive extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('size', ['label' => __('Screen size from'), 'renderer' => false]);
        $this->addColumn('items', ['label' => __('Number of items'), 'renderer' => false]);

        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
    }
}
