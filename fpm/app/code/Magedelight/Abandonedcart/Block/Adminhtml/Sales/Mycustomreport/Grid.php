<?php

namespace Magedelight\Abandonedcart\Block\Adminhtml\Sales\Mycustomreport;

class Grid extends \Magento\Reports\Block\Adminhtml\Grid\AbstractGrid
{
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(true);
    }
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getResourceCollectionName()
    {
        return \Magedelight\Abandonedcart\Model\ResourceModel\Report\MyCustomReport\Collection::class;
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $this->addColumn(
            'period',
            [
                'header' => __('Interval'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date::class,
                'totals_label' => __('Total'),
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );

        $this->addColumn(
            'first_name',
            [
                'header' => __('FirstName'),
                'index' => 'first_name',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-firstName',
                'column_css_class' => 'col-firstName'
            ]
        );

        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-email',
                'column_css_class' => 'col-email'
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product'),
                'index' => 'product_name',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'product_sku',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        $this->addColumn(
            'qty_ordered',
            [
                'header' => __('Order Quantity'),
                'index' => 'qty_ordered',
                'type' => 'number',
                'total' => 'sum',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );

        $this->addExportType('*/*/exportMyCustomReportCsv', __('CSV'));
        $this->addExportType('*/*/exportMyCustomReportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

  /*  protected function _addOrderStatusFilter($collection, $filterData)
    {
        parent::_addOrderStatusFilter($collection, $filterData);
        $collection->setEmail($filterData->getData('email'));
        return $this;
    }*/
}
