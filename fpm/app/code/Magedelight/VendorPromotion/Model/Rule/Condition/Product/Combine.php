<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_VendorPromotion
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\VendorPromotion\Model\Rule\Condition\Product;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($this->getRule()->getVendorId() && !in_array($object->getVendorId(), explode(',', $this->getRule()->getVendorId()))) {
            return false;
        }
        if (!$this->getConditions()) {
            return true;
        }
        return parent::validate($object);
    }
}