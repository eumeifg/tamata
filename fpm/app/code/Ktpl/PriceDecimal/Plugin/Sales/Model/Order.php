<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_PriceDecimal
 * @copyright   Copyright (c) 2019-2020 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace Ktpl\PriceDecimal\Plugin\Sales\Model;

class Order
{
    /**
     * @var \PHPCuong\PriceDecimal\Helper\Data
     */
    protected $priceDecimalHelperData;

    /**
     * @param \PHPCuong\PriceDecimal\Helper\Data $priceDecimalHelperData
     */
    public function __construct(
        \Ktpl\PriceDecimal\Helper\Data $priceDecimalHelperData
    ) {
        $this->priceDecimalHelperData = $priceDecimalHelperData;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param array ...$args
     * @return array
     */
    public function beforeFormatPricePrecision(
        \Magento\Sales\Model\Order $subject,
        ...$args
    ) {
        if ($this->priceDecimalHelperData->isEnable()) {
            if ($this->priceDecimalHelperData->showDecimal()) {
                //change the precision
                $args[1] = $this->priceDecimalHelperData->getDecimalLength();
            } else {
                $args[1] = 0;
            }
        }
        return $args;
    }
}
