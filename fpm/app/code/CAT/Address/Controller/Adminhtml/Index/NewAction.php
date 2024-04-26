<?php

namespace CAT\Address\Controller\Adminhtml\Index;

use CAT\Address\Controller\Adminhtml\City;

class NewAction extends City
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
