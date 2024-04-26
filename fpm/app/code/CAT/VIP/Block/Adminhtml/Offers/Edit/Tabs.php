<?php

namespace CAT\VIP\Block\Adminhtml\Offers\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('vipoffer_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('VIP Offers Information'));
    }
}
