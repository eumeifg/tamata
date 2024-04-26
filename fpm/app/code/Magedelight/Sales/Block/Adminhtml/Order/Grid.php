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

use Magedelight\Sales\Model\Config\Source\Order\CancelledBy;
use Magedelight\Sales\Model\Order as VendorOrder;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var VendorProduct
     */
    protected $vendorOrder;

    /**
     * @var int
     */
    protected $requestStatus;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $vendorModel;

    /**
     * @var CancelledBy
     */
    protected $cancelledBy;

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
        array $data = []
    ) {
        $this->vendorOrder = $vendorOrderFactory->create();
        $this->vendorModel = $vendorModel;
        $this->cancelledBy = $cancelledBy;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->requestStatus = $this->getRequest()->getParam('status', false);
        $this->setId('vendorOrderGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_order_filter');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection()
    {
        $collection = $this->vendorOrder->getCollection();
        if ($this->requestStatus) {
            $collection->addFieldToFilter('main_table.status', ['eq' => $this->requestStatus]);
            if ($this->requestStatus === 'canceled') {
                $collection->getSelect()->joinLeft(
                    ["rvp" => "md_vendor_commission_payment"],
                    "main_table.vendor_order_id = rvp.vendor_order_id",
                    ["cancellation_fee" => "rvp.cancellation_fee"]
                );
            }
        } else {
            $collection->getSelect()->joinLeft(
                ["sales_order"=>$collection->getTable('sales_order')],
                "main_table.order_id = sales_order.entity_id",
                [
                    'main_table.status'=>'main_table.status',
                    'main_table.is_confirmed'=>'main_table.is_confirmed',
                    'sales_order.is_confirmed'=>'sales_order.is_confirmed'
                ]
            );
            $collection->addFieldToFilter(
                'main_table.status',
                ['nin' => [VendorOrder::STATUS_CANCELED, VendorOrder::STATUS_CLOSED, VendorOrder::STATUS_COMPLETE]]
            );
        }
        $collection->addFilterToMap('store_id', 'main_table.store_id');
        $collection->addFilterToMap('increment_id', 'main_table.increment_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
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
                        VendorOrder::STATUS_IN_TRANSIT => __('In Transit')
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

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendoer_order_id');
        $this->getMassactionBlock()->setFormFieldName('vendor_order');

        /*$this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Disable'),
                'url' => $this->getUrl('*//*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );*/

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        $params = [
            'store' => $this->getRequest()->getParam('store'),
            'order_id' => $row->getOrderId(),
            'vendor_id' =>$row->getVendorId()
        ];
        $status = $this->getRequest()->getParam('status', false);
        if ($status) {
            $params['status'] = $status;
        }
        $params['vendor_order_id'] = $row->getId();
        return $this->getUrl('*/*/view', $params);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::order_manage');
    }
}
