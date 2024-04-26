<?php

namespace MDC\Catalog\Block\Adminhtml\Product;


use Magedelight\Catalog\Model\Product as VendorProduct;

class Grid extends \Magedelight\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * @var VendorProduct
     */
    protected $vendorProduct;
    protected $_storeId = 0;

    /**
     * @var int
     */
    protected $requestStatus;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $vendorModel;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param VendorProduct $vendorProduct
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        VendorProduct $vendorProduct,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $vendorProduct, $vendorModel, $productWebsiteRepository, $systemStore, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->requestStatus = (int)$this->getRequest()->getParam(
            VendorProduct::STATUS_PARAM_NAME,
            VendorProduct::STATUS_UNLISTED
        );
        $this->setId('vendorProductGrid');
        $this->setDefaultSort('vendor_product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_product_filter');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return \Magedelight\Catalog\Block\Adminhtml\Product\Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'store_id') {
                $this->getCollection()->addFieldToFilter('rbvps.store_id', ['eq' => $column->getFilter()->getValue()]);
            } else {
                parent::_addColumnFilterToCollection($column);
            }
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->vendorProduct->getCollection();
        $collection->addFieldToFilter('main_table.type_id', ['neq' => 'configurable']);
        $status =  $this->getRequest()->getParam('status');
        $collection->addFilterToMap('vendor_product_id', 'main_table.vendor_product_id');
        $collection->addStatusFilter($collection, $status);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareColumns()
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
            'image',
            [
                'header' => __('Image'),
                'class' => 'Image',
                'name' => 'Image',
                'filter' => false,
                'sortable' => false,
                'renderer' => \Magedelight\Catalog\Block\Adminhtml\Product\Renderer\Image::class
            ]
        );

        $this->addColumn(
            'vendor_id',
            [
                'header' => __('Vendor'),
                'index' => 'business_name',
                'filter_index' => 'rvwd.business_name',
            ]
        );

        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'product_name',
                'filter_index' => 'product_name'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'filter_index' => 'price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
                'type' => 'price'
            ]
        );
        $this->addColumn(
            'special_price',
            [
                'header' => __('Special Price'),
                'index' => 'special_price',
                'filter_index' => 'special_price',
                'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
                'type' => 'price'
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

        $this->addColumn(
            'cost_price_iqd',
            [
                'header' => __('Cost Price (IQD)'),
                'index' => 'cost_price_iqd',
            ]
        );

        $this->addColumn(
            'cost_price_usd',
            [
                'header' => __('Cost Price (USD)'),
                'index' => 'cost_price_usd',
            ]
        );

        $keys = array_column($this->systemStore->getWebsiteValuesForForm(), 'value');
        $values = array_column($this->systemStore->getWebsiteValuesForForm(), 'label');

        $this->addColumn(
            'website',
            [
                'header' => __('Websites'),
                'width' => '100px',
                'sortable' => false,
                'filter_index' => 'rbvpw.website_id',
                'index' => 'website_id',
                'type' => 'options',
                'renderer' => \Magedelight\Catalog\Block\Adminhtml\Product\Renderer\Website::class,
                'options' => array_combine($keys, $values)
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
                'renderer'  => \Magedelight\Catalog\Block\Adminhtml\Product\Renderer\Actionlink::class,
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }
}
