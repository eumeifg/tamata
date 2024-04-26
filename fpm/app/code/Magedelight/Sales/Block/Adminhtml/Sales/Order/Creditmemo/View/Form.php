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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Creditmemo\View;

/**
 * Creditmemo view form
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Form
{
    /**
     * @var \Magedelight\Sales\Model\Order
     */
    protected $vendorOrder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magedelight\Sales\Model\Order $vendorOrder,
        array $data = []
    ) {
        $this->vendorOrder = $vendorOrder;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * @return \Magedelight\Sales\Model\Order
     */
    public function getVendorOrder()
    {
        $orderId = $this->getOrder()->getId();
        $vendorId = $this->getCreditmemo()->getData('vendor_id');
        return $this->vendorOrder->getByOriginOrderId($orderId, $vendorId);
    }

    /**
     * Retrieve order url
     *
     * @return string
     */
    public function getOrderUrl()
    {
        return $this->getUrl(
            'rbsales/order/view',
            ['order_id' => $this->getCreditmemo()->getOrderId(), 'vendor_id' => $this->getCreditmemo()->getVendorId()]
        );
    }
}
