<?php

namespace CAT\SearchPage\Controller\Adminhtml\Items;

class NewAction extends \CAT\SearchPage\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
