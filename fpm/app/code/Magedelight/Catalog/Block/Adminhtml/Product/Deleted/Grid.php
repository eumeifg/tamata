<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Catalog
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Catalog\Block\Adminhtml\Product\Deleted;

use Magedelight\Catalog\Model\Product as VendorProduct;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var VendorProduct
     */
    private $vendorProduct;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    private $vendorModel;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        array $data = []
    ) {
        $this->vendorProduct = $vendorProductFactory->create();
        $this->vendorModel = $vendorModel;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorProductGrid');
        $this->setDefaultSort('main_table.vendor_product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_product_filter');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        /* if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                        'websites', 'catalog_product_website', 'website_id', 'product_id=entity_id', null, 'left'
                );
            }
        } */
        return parent::_addColumnFilterToCollection($column);
    }

    protected function _prepareCollection()
    {
        $collection = $this->vendorProduct->getCollection();
        $collection->addFieldToFilter('is_deleted', ['eq' => 1]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'vendor_product_id',
            [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'vendor_product_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'vendor_id',
            [
            'header' => __('Vendor'),
            'index' => 'vendor_id',
            'filter_index' => 'vendor_id',
            'type' => 'options',
            'options' => $this->vendorModel->getVendorOptions()
            ]
        );
        
        $this->addColumn(
            'name',
            [
            'header' => __('Product Name'),
            'index' => 'name',
            'filter_index' => 'name'
            ]
        );
        
        $this->addColumn(
            'qty',
            [
            'header' => __('QTY'),
            'index' => 'qty',
            ]
        );

        $this->addColumn(
            'vendor_sku',
            [
            'header' => __('Product SKU'),
            'index' => 'vendor_sku',
            ]
        );
        
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/deletedgrid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
}
