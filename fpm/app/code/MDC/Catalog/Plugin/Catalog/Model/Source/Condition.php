<?php

namespace MDC\Catalog\Plugin\Catalog\Model\Source;

class Condition
{
    public function afterGetAllOptions(
        \Magedelight\Catalog\Model\Source\Condition $subject,
        $result
    ) {
        $tmp = $result[0];
        $result[0] = $result[1];
        $result[1] = $tmp;
        return $result;
    }
}