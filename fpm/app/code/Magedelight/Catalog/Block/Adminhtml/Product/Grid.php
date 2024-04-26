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
namespace Magedelight\Catalog\Block\Adminhtml\Product;

use Magedelight\Catalog\Model\Product as VendorProduct;

/**
 * @author Rocket Bazaar Core Team
 * Created at 11 April, 2016 06:03:12 PM
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
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
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Catalog\Model\Product $vendorProduct,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magedelight\Catalog\Api\ProductWebsiteRepositoryInterface $productWebsiteRepository,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->vendorProduct = $vendorProduct;
        $this->productWebsiteRepository = $productWebsiteRepository;
        $this->vendorModel = $vendorModel;
        $this->systemStore = $systemStore;
        parent::__construct($context, $backendHelper, $data);
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
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magedelight_Catalog::vendor_products_listed');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
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
     * @return $this
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

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('vendor_product_id');
        $this->getMassactionBlock()->setFormFieldName('vendor_product');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        //array_unshift($statuses, array('label' => '', 'value' => ''));
        if ($this->requestStatus === VendorProduct::STATUS_UNLISTED) {
            $this->getMassactionBlock()->addItem(
                'list',
                [
                    'label' => __('List'),
                    'url' => $this->getUrl('*/*/massList')
                ]
            );
        } elseif ($this->requestStatus === VendorProduct::STATUS_LISTED) {
            $this->getMassactionBlock()->addItem(
                'unlist',
                [
                    'label' => __('UnList'),
                    'url' => $this->getUrl('*/*/massUnList')
                ]
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    public function getRowUrl($row)
    {
        $this->_storeId = $this->getProductWebsite($row->getId());
        if (!$this->requestStatus) {
            return $this->getUrl(
                '*/*/edit',
                [
                    'id' => $row->getId()
                ]
            );
        }
        return 'javascript:void(0)';
    }
}
