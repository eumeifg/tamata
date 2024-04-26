<?php

namespace Ktpl\Quickview\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Buttonstyle
 *
 * @package Ktpl\Quickview\Model\Config\Source
 */
class Buttonstyle implements ArrayInterface
{

    /**
     * Return list of Buttonstyle Style Options
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'v1',
                'label' => 'Version 1',
            ],
            [
                'value' => 'v2',
                'label' => 'Version 2',
            ]
        ];
    }
}
