<?php

namespace MDC\Sales\Block\Adminhtml\Order;

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrder;

class Grid extends \Magedelight\Sales\Block\Adminhtml\Order\Grid
{

    /**
     * @var \MDC\Sales\Model\Source\Order\PickupStatus
     */
    protected $pickupStatuses;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param CancelledBy $cancelledBy
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Sales\Model\OrderFactory $vendorOrderFactory,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        CancelledBy $cancelledBy,
        \MDC\Sales\Model\Source\Order\PickupStatus $pickupStatuses,
        array $data = []
    ) {
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->vendorModel = $vendorModel;
        $this->cancelledBy = $cancelledBy;
        $this->pickupStatuses = $pickupStatuses;
        parent::__construct($context, $backendHelper, $vendorOrderFactory, $vendorModel, $cancelledBy, $data);
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Vendor Order ID'),
                'index' => 'increment_id',
                'filter_index' => 'increment_id'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __(' Purchased On'),
                'index' => 'created_at',
                'type'  => 'datetime',
                'renderer'     => \Magedelight\Sales\Block\Adminhtml\Order\Renderer\Dateformate::class,
                'filter_index' => 'main_table.created_at'
            ]
        );
        $this->addColumn(
            'vendor_id',
            [
                'header' => __('Vendor'),
                'index' => 'vendor_id',
                'filter_index' => 'main_table.vendor_id',
                'type' => 'options',
                'options' => $this->vendorModel->getVendorOptions(null, true)
            ]
        );
        if ($this->requestStatus !== 'canceled' && $this->requestStatus !== 'complete') {
            $this->addColumn(
                'admin_is_confirmed',
                [
                    'header' => __('Admin Confirm'),
                    'index' => 'sales_order.is_confirmed',
                    'filter_index' => 'sales_order.is_confirmed',
                    'type' => 'options',
                    'options' => [0 => "No", 1 => "Yes"]
                ]
            );
            $this->addColumn(
                'is_confirmed',
                [
                    'header' => __('Vendor Confirm'),
                    'index' => 'main_table.is_confirmed',
                    'filter_index' => 'main_table.is_confirmed',
                    'type' => 'options',
                    'options' => [0 => "No", 1 => "Yes"]
                ]
            );
            $this->addColumn(
                'status',
                [
                    'header' => __('Status'),
                    'index' => 'main_table.status',
                    'filter_index' => 'main_table.status',
                    'type' => 'options',
                    'options' => [
                        VendorOrder::STATUS_PENDING => __('New'),
                        VendorOrder::STATUS_CONFIRMED => __('Confirmed'),
                        VendorOrder::STATUS_PROCESSING => __('Processing'),
                        VendorOrder::STATUS_PACKED => __('Packed'),
                        VendorOrder::STATUS_SHIPPED => __('Handover'),
                        VendorOrder::STATUS_IN_TRANSIT => __('In Transit'),
                        VendorOrder::STATUS_OUT_WAREHOUSE => __('Out For Delievery')
                    ]
                ]
            );
        }

        if ($this->requestStatus === 'complete') {
            $this->addColumn(
                'status',
                [
                    'header' => __('Status'),
                    'index' => 'status',
                    'filter_index' => 'status',
                    'type' => 'options',
                    'options' => [
                        VendorOrder::STATUS_COMPLETE => __('Delivered'),
                        VendorOrder::STATUS_CLOSED => __('Closed')
                    ]
                ]
            );
            $this->addColumn(
                'total_refunded',
                [
                    'header' => __('Total Refunded'),
                    'index' => 'total_refunded',
                    'filter_index' => 'total_refunded'
                ]
            );
        }

        if ($this->requestStatus === 'canceled') {
            $this->addColumn(
                'cancelled_by',
                [
                    'header' => __('Cancelled By'),
                    'index' => 'cancelled_by',
                    'filter_index' => 'cancelled_by',
                    'type' => 'options',
                    'options' => $this->cancelledBy->getOptionArray()
                ]
            );
        }

        $this->addColumn(
            'grand_total',
            [
                'header' => __('Total'),
                'index' => 'grand_total',
                'filter_index' => 'main_table.grand_total',
                'currency_code' => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                'type' => 'price'
            ]
        );

        $this->addColumn(
            'store_id',
            ['header' => __('Purchase Point'), 'index' => 'store_id', 'type' => 'store', 'store_view' => true]
        );

        if ($this->requestStatus === 'canceled') {
            $this->addColumn(
                'cancellation_fee',
                [
                    'header' => __('Cancellation Fee'),
                    'index' => 'cancellation_fee',
                    'filter_index' => 'cancellation_fee',
                    'currency_code' => $this->_storeManager->getStore()->getCurrentCurrency()->getCode(),
                    'type' => 'price'
                ]
            );
        }


        $this->addColumn(
            'pickup_status',
            [
                'header' => __('Pickup Status'),
                'index' => 'pickup_status',
                'filter_index' => 'main_table.pickup_status',
                'type' => 'options',
                'options' => $this->pickupStatuses->getOptionArray()
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'filter'    => false,
                'sortable'  => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
                'renderer'  => \Magedelight\Sales\Block\Adminhtml\Order\Renderer\Actionlink::class,
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return $this;
    }
}
