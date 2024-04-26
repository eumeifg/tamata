<?php

namespace Ktpl\Pushnotification\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TypePromotion implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $res = [];
        $res[] = ['value' => 'none', 'label' => 'Select Type'];
        $res[] = ['value' => 'product', 'label' => 'Product promotions'];
        $res[] = ['value' => 'category', 'label' => 'Category promotions'];
        $res[] = ['value' => 'microsite', 'label' => 'Vendor promotions'];

        return $res;
    }
}
