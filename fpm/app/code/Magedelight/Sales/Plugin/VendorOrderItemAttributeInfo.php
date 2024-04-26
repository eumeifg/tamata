<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Plugin;

class VendorOrderItemAttributeInfo extends AbstractOrderItemAttributeInfo
{
    /**
     * Get Items
     *
     * @return \Magedelight\Sales\Api\Data\OrderItemInterface[]
     */
    public function afterGetItems(
        \Magedelight\Sales\Api\Data\VendorOrderInterface $subject,
        $result
    ) {
        return $this->setAttributesInfo($result);
    }
}
