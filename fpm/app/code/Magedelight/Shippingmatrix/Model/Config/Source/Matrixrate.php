<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Model\Config\Source;

class Matrixrate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate
     */
    protected $_carrierMatrixrate;

    /**
     * @param \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $carrierMatrixrate
     */
    public function __construct(\Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $carrierMatrixrate)
    {
        $this->_carrierMatrixrate = $carrierMatrixrate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierMatrixrate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
