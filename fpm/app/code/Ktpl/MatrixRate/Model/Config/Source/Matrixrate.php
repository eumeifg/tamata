<?php

namespace Ktpl\MatrixRate\Model\Config\Source;

class Matrixrate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Ktpl\MatrixRate\Model\Carrier\Matrixrate
     */
    protected $carrierMatrixrate;

    /**
     * @param \Ktpl\MatrixRate\Model\Carrier\Matrixrate $carrierMatrixrate
     */
    public function __construct(\Ktpl\MatrixRate\Model\Carrier\Matrixrate $carrierMatrixrate)
    {
        $this->carrierMatrixrate = $carrierMatrixrate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->carrierMatrixrate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
