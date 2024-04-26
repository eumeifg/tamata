<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ktpl\ReorderItem\Block\Order\Item\Renderer;

use Magento\Sales\Model\Order\CreditMemo\Item as CreditMemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Customer\Model\Context;

/**
 * Order item render block
 *
 * @api
 * @since 100.0.2
 */
class DefaultRenderer extends \Magento\Framework\View\Element\Template
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->string = $string;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    /**
     * Get url for reorder action
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReorderItemUrl($order, $itemId)
    {
        if (!$this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return $this->getUrl('reorderitem/guest/reorder', ['order_id' => $order->getId(), 'item_id' => $itemId]);
        }
        return $this->getUrl('reorderitem/order/reorder', ['order_id' => $order->getId(), 'item_id' => $itemId]);
    }
}
