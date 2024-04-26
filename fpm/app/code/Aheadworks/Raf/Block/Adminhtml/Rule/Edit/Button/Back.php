<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Back
 * @package Aheadworks\Raf\Block\Adminhtml\Rule\Edit\Button
 */
class Back extends AbstractButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->isAllowedToShowButton()) {
            $data = [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
                'class' => 'back',
                'sort_order' => 10
            ];
        }
        return $data;
    }
}
