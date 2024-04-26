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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order\Shipment\View;

/**
 * Shipment view form
 *
 * @author Rocket Bazaar Core Team
 */
class Form extends \Magento\Shipping\Block\Adminhtml\View\Form
{
    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    protected $vendorOrderRepository;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        array $data = []
    ) {
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct($context, $registry, $adminHelper, $carrierFactory, $data);
    }

    /**
     * @return \Magedelight\Sales\Model\Order
     */
    public function getVendorOrder()
    {
        return $this->vendorOrderRepository->getById(
            $this->getShipment()->getData('vendor_order_id')
        );
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
            [
                'order_id' => $this->getInvoice()->getOrderId(),
                'vendor_id' => $this->getInvoice()->getVendorId()
            ]
        );
    }
}
