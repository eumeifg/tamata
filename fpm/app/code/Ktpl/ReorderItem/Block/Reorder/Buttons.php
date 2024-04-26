<?php

/**
 * Block of links in Order view page
 */
namespace Ktpl\ReorderItem\Block\Reorder;

use Magento\Customer\Model\Context;

class Buttons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Ktpl_ReorderItem::order/buttons.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);        
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getItem()
    {
        return $this->getParentBlock()->getOrderItem();
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
