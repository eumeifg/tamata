<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Reset
 * @package Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button
 */
class Reset extends AbstractButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/*', ['_current' => true])),
            'sort_order' => 30,
            'id' => ''
        ];
    }
}
