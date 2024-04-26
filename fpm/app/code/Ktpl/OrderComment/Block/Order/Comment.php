<?php
/**
 * Copyright © Krish TechnoLabs, All rights reserved.
 */
declare(strict_types=1);

namespace Ktpl\OrderComment\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Ktpl\OrderComment\Api\Data\OrderCommentInterface;

class Comment extends Template
{
    /**
     * @var string
     */
    protected $_template = 'order/comment.phtml';

    /**
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get Order
     *
     * @return array|null
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get Order Comment
     *
     * @return string
     */
    public function getOrderComment()
    {
        $orderComment = $this->getOrder()->getData(OrderCommentInterface::COLUMN_NAME);
        return isset($orderComment) ? trim($orderComment) : "";
    }

    /**
     * Retrieve html comment
     *
     * @return string
     */
    public function getOrderCommentHtml()
    {
        return nl2br($this->escapeHtml($this->getOrderComment()));
    }
}
