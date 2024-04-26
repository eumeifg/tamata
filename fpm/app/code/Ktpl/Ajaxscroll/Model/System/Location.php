<?php

namespace Ktpl\Ajaxscroll\Model\System;

class Location extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions() 
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Left'), 'value' => 'left'],
                ['label' => __('Right'), 'value' => 'right'],
            ];
        }
        return $this->_options;
    }
}