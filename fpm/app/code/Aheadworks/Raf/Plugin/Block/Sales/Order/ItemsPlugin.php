<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Raf\Plugin\Block\Sales\Order;

use Magento\Bundle\Model\Product\Type as BundleProduct;
use Magento\Sales\Block\Adminhtml\Order\View\Items;

/**
 * Class ItemsPlugin
 *
 * @package Aheadworks\Raf\Plugin\Block\Sales\Order
 */
class ItemsPlugin
{
    /**
     * Add RAF column after discount
     *
     * @param Items $subject
     * @param \Closure $proceed
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetColumns(
        Items $subject,
        \Closure $proceed
    ) {
        $columns = $proceed();
        foreach ($subject->getOrder()->getAllItems() as $orderItem) {
            if ($orderItem->getProductType() == BundleProduct::TYPE_CODE) {
                return $columns;
            }
        }
        $newColumns = [];
        foreach ($columns as $key => $column) {
            $newColumns[$key] = $column;
            if ($key == 'discont') {
                $newColumns['aw-raf'] = __('Referral Discount');
            }
        }
        return $newColumns;
    }
}
