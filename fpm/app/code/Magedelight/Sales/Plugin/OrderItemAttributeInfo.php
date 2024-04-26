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

class OrderItemAttributeInfo extends AbstractOrderItemAttributeInfo
{
    /**
     * Get Items
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function afterGetItems(
        \Magento\Sales\Api\Data\OrderInterface $subject,
        $result
    ) {
        return $this->setAttributesInfo($result);
    }
}
