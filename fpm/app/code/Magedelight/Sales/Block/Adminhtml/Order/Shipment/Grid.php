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
namespace Magedelight\Sales\Block\Adminhtml\Order\Shipment;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'vendor_order_id',
            $this->getRequest()->getParam('vendor_order_id')
        );
        $collection->getSelect()->joinLeft(
            ['oce' => 'sales_order'],
            "main_table.order_id = oce.entity_id",
            [
            'CONCAT(oce.customer_firstname," ", oce.customer_lastname) as customer_name',
            'oce.customer_firstname',
            'oce.customer_lastname',
            'oce.created_at as order_date'
                ]
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'increment_id',
            [
            'header' => __('Shipment'),
            'index' => 'increment_id',
            'filter_index' => 'increment_id',
            'filter' => false,
            'sortable' => false
                ]
        );
        $this->addColumn(
            'created_at',
            [
            'header' => __('Ship Date'),
            'index' => 'created_at',
            'type' => 'date',
            'renderer' => \Magedelight\Sales\Block\Adminhtml\Order\Renderer\Dateformate::class,
            'filter_index' => 'main_table.created_at',
            'filter' => false,
            'sortable' => false
                ]
        );
        $this->addColumn(
            'order_id',
            [
            'header' => __('Order #'),
            'index' => 'order_id',
            'filter_index' => 'order_id',
            'filter' => false,
            'sortable' => false
                ]
        );
        $this->addColumn(
             'order_date',
             [
             'header' => __('Order Date'),
             'index' => 'order_date',
             'type' => 'date',
             'renderer' => \Magedelight\Sales\Block\Adminhtml\Order\Renderer\Dateformate::class,
             'filter_index' => 'oce.order_date',
             'filter' => false,
             'sortable' => false
                ]
         );
        $this->addColumn(
            'customer_name',
            [
            'header' => __('Ship-to Name'),
            'index' => 'customer_name',
            'filter_index' => 'oce.customer_name',
            'filter' => false,
            'sortable' => false
                ]
        );

        $this->addColumn(
            'total_qty',
            [
            'header' => __('Total Quantity'),
            'index' => 'total_qty',
            'filter_index' => 'total_qty',
            'filter' => false,
            'sortable' => false
                ]
        );
        $this->addColumn('action', [
            'header' => __('Action'),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'header_css_class' => 'col-action',
            'column_css_class' => 'col-action',
            'renderer' => \Magedelight\Sales\Block\Adminhtml\Order\Renderer\Shipmentlink::class,
        ]);

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/shipment/view',
            ['store' => $this->getRequest()->getParam('store'), 'shipment_id' => $row->getIncrementId()]
        );
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Sales::order_manage');
    }
}
