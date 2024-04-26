<?php

namespace Ktpl\Pushnotification\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PushSendTo implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $res = [];
        $res[] = ['value' => 'all', 'label' => 'All'];
        $res[] = ['value' => 'customer_group', 'label' => 'Customer Group'];
        $res[] = ['value' => 'customer', 'label' => 'Customer'];

        return $res;
    }
}
