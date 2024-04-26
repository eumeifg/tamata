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

class AppendVendorIdForReorder
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return $this
     */
    public function beforeAddOrderItem(
        \Magento\Checkout\Model\Cart $subject,
        $orderItem,
        $qtyFlag = null
    ) {
        /* When reorder done multiple time, same product getting added twice in cart if vendor_id not found for the product during product load. Reference catalog_product_load_after event in base suite.
        */
        $this->request->setParam('vendor_id', $orderItem->getVendorId());
        return [$orderItem, $qtyFlag];
    }
}
