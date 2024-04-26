<?php

namespace CAT\Custom\Plugin;

use Magento\Catalog\Ui\Component\Product\MassAction as ProductMassAction;

class MassAction
{
    /**
     * @param ProductMassAction $subject
     * @param $result
     * @param $actionType
     * @return false|mixed
     */
    public function afterIsActionAllowed(ProductMassAction $subject, $result, $actionType)
    {
        if (in_array($actionType, ["delete", "attributes"])) {
            return false;
        }
        return $result;
    }
}
