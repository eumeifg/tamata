<?php

namespace MDC\Sales\Block\Adminhtml\Sales\Order\View;

class Items extends \Magedelight\Sales\Block\Adminhtml\Sales\Order\View\Items
{

    /**
     * @var \Magento\Backend\Block\Widget\Button
     */
    private $buttonWidget;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    private $salesOrderConfig;

    /**
     * @var \Magedelight\Sales\Model\Order
     */
    private $vendorOrder;

    /**
     * @var \Magedelight\Vendor\Model\VendorRepository
     */
    private $vendorRepository;
    /**
     * @var \Magedelight\Sales\Api\OrderRepositoryInterface
     */
    private $vendorOrderRepository;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order\Config $salesConfig
     * @param \Magedelight\Vendor\Model\VendorRepository $vendorRepository
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magento\Backend\Block\Widget\Button $buttonWidget
     * @param \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\Config $salesConfig,
        \Magedelight\Vendor\Model\VendorRepository $vendorRepository,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magento\Backend\Block\Widget\Button $buttonWidget,
        \Magedelight\Sales\Api\OrderRepositoryInterface $vendorOrderRepository,
        array $data = []
    ) {
        $this->vendorRepository = $vendorRepository;
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->salesOrderConfig = $salesConfig;
        $this->vendorOrderRepository = $vendorOrderRepository;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $salesConfig,
            $vendorRepository,
            $vendorOrderFactory,
            $buttonWidget,
            $vendorOrderRepository,
            $data
        );
        $this->buttonWidget = $buttonWidget;
    }

    public function _getButtonsHtml(
        \Magedelight\Sales\Model\Order $vendorOrder,
        \Magento\Sales\Model\Order $order,
        $vendorId,
        $_item
    ) {
        $buttonGroups = [];
        $urlParams = [
            'order_id'=> $order->getId(),
            'do_as_vendor' => $vendorId,
            'vendor_order_id' => $_item->getVendorOrderId()
        ];
        if ($vendorOrder->canConfirm($_item)) {
            $message = __('Are you sure you want to confirm this order?');
            $this->buttonWidget
            ->setData([
                    'id'      => 'order_confirm_' . $vendorId,
                    'label'   => __('Confirm'),
                    'onclick'   => 'deleteConfirm(\'' . $message . '\', \'' . $this->getUrl('rbsales/order_confirm/save', $urlParams) . '\')',
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }

        if ($vendorOrder->canCancel()) {
            $message = __('Are you sure you want to cancel this order?');
            $this->buttonWidget
            ->setData([
                    'id'      => 'order_cancel_' . $vendorId,
                    'label'   => __('Cancel'),
                    'onclick'   => 'deleteConfirm(\'' . $message . '\', \'' . $this->getUrl('rbsales/order/cancel', $urlParams) . '\')',
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }

         /*if ($vendorOrder->canInvoice()) {
            $_label = $order->getForcedDoShipmentWithInvoice() ? __('Invoice and Ship') : __('Invoice');
            $this->buttonWidget
            ->setData([
                    'id'      => 'order_invoice_' . $vendorId,
                    'label'     => $_label,
                    'onclick'   => 'setLocation(\'' . $this->getUrl('rbsales/order_invoice/start', $urlParams) . '\')',
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }*/  /* Disabled as combine invoice and ship featured is added */

        /*if ($vendorOrder->canShip() && !$order->getForcedDoShipmentWithInvoice()) {
            $this->buttonWidget
            ->setData([
                    'id'        => 'order_ship_' . $vendorId,
                    'label'     => __('Ship'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('rbsales/order_shipment/new', $urlParams) . '\')',
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }*/ /* Disabled as combine invoice and ship featured is added */

        /*Do generate invoice and shipment together direct without loading create page*/
        if ($vendorOrder->canInvoice() || $vendorOrder->canShip() ) {

            if($vendorOrder->canInvoice() ){
                $button_label = "Invoice and Ship";
            }else{
                $button_label = "Ship";
            }
            $this->buttonWidget
            ->setData([
                    'id'      => 'order_invoice_' . $vendorId,
                    'label'     =>  __($button_label),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('rbsales/order_invoiceandship/index', $urlParams) . '\')',
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }
        /*Do generate invoice and shipment together direct without loading create page*/


        /* if ($vendorOrder->getStatus() == \Magedelight\Sales\Model\Order::STATUS_HANDOVER ||
            $vendorOrder->getStatus() == \Magedelight\Sales\Model\Order::STATUS_SHIPPED) {
            $this->buttonWidget
            ->setData([
                    'id'        => 'order_ship_' . $vendorId,
                    'label'     => __('In Transit'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('rbsales/order/intransit', $urlParams) . '\')',
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        } */

        if ($vendorOrder->getStatus() == \Magedelight\Sales\Model\Order::STATUS_IN_TRANSIT || $vendorOrder->getStatus() == \Magedelight\Sales\Model\Order::STATUS_OUT_WAREHOUSE) {
            $this->buttonWidget
            ->setData([
                    'id'        => 'order_ship_' . $vendorId,
                    'label'     => __('Delivered'),
                    'onclick'   => 'setLocation(\'' . $this->getUrl('rbsales/order/delivered', $urlParams) . '\')',
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }

        if ($vendorOrder->canCreditmemo()) {
            $message = __('This will create an offline refund. To create an online refund,
             open an invoice and create credit memo for it. Do you wish to proceed?');
            $urlParams['_current'] = true;
            $creditMemoUrl = $this->getUrl('rbsales/order_creditmemo/start', $urlParams);
            $onClick = "setLocation('{$creditMemoUrl}')";
            if ($order->getPayment()->getMethodInstance()->isGateway()) {
                $onClick = "confirmSetLocation('{$message}', '{$creditMemoUrl}')";
            }
            $this->buttonWidget
            ->setData([
                    'id'        => 'order_creditmemo_' . $vendorId,
                    'label'     => __('Credit Memo'),
                    'onclick'   => $onClick,
                    'class'     => 'go'
            ]);
            $buttonGroups[] = $this->buttonWidget->toHtml();
        }

        if (!empty($buttonGroups)) {
            return '<p class="form-buttons">' . implode("\n", $buttonGroups) . '</p>';
        } else {
            return '';
        }
    }

    public function massInvoiceShipUrl(){

        return $this->getUrl('rbsales/order_invoiceandship/index');
    }

    public function massCancelUrl(){

        return $this->getUrl('rbsales/order/masscancel');
    }
}
