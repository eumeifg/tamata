<?php

namespace Ktpl\Ajaxscroll\Model\System;

class Goupspeed extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions() 
    {
        if (null === $this->_options) {
            $this->_options = [
                ['label' => __('Slow'), 'value' => 'slow'],
                ['label' => __('Fast'), 'value' => 'fast'],
            ];
        }
        return $this->_options;
    }
}