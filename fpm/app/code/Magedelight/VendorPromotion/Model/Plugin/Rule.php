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
namespace Magedelight\VendorPromotion\Model\Plugin;

class Rule
{
    /**
     * Save/delete coupon
     *
     * @return $this
     */
    public function beforeSave(
        \Magento\SalesRule\Model\Rule $subject
    ) {
        if ($subject->getVendorId() && is_array($subject->getVendorId())) {
            $vendorIds = implode(',', $subject->getVendorId());
            if (!empty($vendorIds)) {
                $subject->setVendorId($vendorIds);
            }
        }
        return [$subject];
    }
}
