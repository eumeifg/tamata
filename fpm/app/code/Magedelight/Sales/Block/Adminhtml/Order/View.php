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
namespace Magedelight\Sales\Block\Adminhtml\Order;

class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $salesConfig,
            $reorderHelper,
            $data
        );
        $vendorOrder =  $this->_coreRegistry->registry('vendor_order');
        if ($vendorOrder) {
            $this->buttonList->remove('order_hold');
            $this->buttonList->remove('order_cancel');
            $this->buttonList->remove('order_unhold');
            $this->buttonList->remove('order_invoice');
            $this->buttonList->remove('order_creditmemo');
            $this->buttonList->remove('order_ship');
            $this->buttonList->remove('order_reorder');
            $this->buttonList->remove('order_edit');
            $this->buttonList->remove('send_notification');

            if ($vendorOrder->isCanceled()) {
                $this->buttonList->remove('send_notification');
            }
        }
    }

    public function getInvoiceUrl()
    {
        $vendorOrder =$this->_coreRegistry->registry('vendor_order');
        if ($vendorOrder && $vendorOrder->getVendorId()) {
            return $this->getUrl('*/order_invoice/new', ['do_as_vendor'=>$vendorOrder->getVendorId()]);
        }
        return $this->getUrl('*/order_invoice/new');
    }

    /**
     * Return back url for view grid
     *
     * @return string
     */
    public function getBackUrl()
    {
        $status = $this->getRequest()->getParam('status', false);
        if ($status) {
            return $this->getUrl('*/*/', ['status' => $status]);
        }
        return $this->getUrl('*/*/');
    }
}
