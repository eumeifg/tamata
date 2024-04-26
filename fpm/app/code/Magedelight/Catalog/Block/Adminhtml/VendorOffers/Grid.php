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
namespace Magedelight\Catalog\Block\Adminhtml\VendorOffers;

use Magedelight\Catalog\Model\Product as VendorProduct;
use Magento\Config\Model\Config\Source\Yesno;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var VendorProduct
     */
    protected $vendorProduct;

    /**
     * @var int
     */
    protected $requestStatus;

    /**
     * @var \Magedelight\Vendor\Model\Vendor
     */
    protected $vendorModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Yesno $yesno
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magento\Framework\Registry $coreRegistry,
        Yesno $yesno,
        array $data = []
    ) {
        $this->vendorProduct = $vendorProductFactory->create();
        $this->vendorModel = $vendorModel;
        $this->coreRegistry = $coreRegistry;
        $this->yesno = $yesno;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorOfferGrid');
        $this->setDefaultSort('main_table.vendor_product_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_offer_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->vendorProduct->getCollection();
        $collection->addFieldToFilter('is_deleted', ['eq' => 0]);
        $storeId = ($this->getRequest()->getParam('store')) ? $this->getRequest()->getParam('store') : 0;
        if ($storeId == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $collection->addFieldToFilter('store_id', ['neq' => \Magento\Store\Model\Store::DEFAULT_STORE_ID]);
        } else {
            $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        }
        $collection->addFieldToFilter('main_table.type_id', ['neq' => 'configurable']);
        $collection->addFieldToFilter('main_table.marketplace_product_id', $this->getRequest()->getParam('id'));
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
            'vendor_id',
            [
            'header' => __('Vendor'),
            'index' => 'vendor_id',
            'filter_index' => 'vendor_id',
            'type' => 'options',
            'options' => $this->vendorModel->getVendorOptions(null, true)
            ]
        );

        $this->addColumn(
            'vendor_sku',
            [
            'header' => __('Vendor SKU'),
            'index' => 'vendor_sku',
            ]
        );

//        $this->addColumn(
//            'name', array(
//            'header' => __('Product Name'),
//            'index' => 'name',
//            'filter_index' => 'name'
//            )
//        );
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
            'header' => __('Units Available'),
            'index' => 'qty',
            ]
        );

        $this->addColumn('store_id', [
            'header' => __('Store View'),
            'index' => 'store_id',
            'type' => 'store',
            'width' => '100px',
            'store_view'=> true,
            'display_deleted' => false,
            'renderer' => \Magedelight\Theme\Block\Adminhtml\Renderer\Store::class,
        ]);

        $this->addColumn('status', [
            'header' => __('Is Listed'),
            'index' => 'status',
            'filter_index' => 'status',
            'type' => 'options',
            'options' => $this->yesno->toArray()
        ]);

        $this->addColumn(
            'edit',
            [
            'renderer' => \Magedelight\Catalog\Block\Adminhtml\Widget\Grid\Column\Renderer\Action::class,
            'header' => __('Edit'),
            'data_attribute' =>  [
                'mage-init' => [
                    'vendorOffers' => [
                        'url' => 'rbcatalog/offer/edit',
                        'mode' => 'edit'
                    ],
                ],
            ]
            ]
        );

        $this->addColumn(
            'edit',
            [
                'renderer' => \Magedelight\Catalog\Block\Adminhtml\Widget\Grid\Column\Renderer\DeleteAction::class,
                'header' => __('Action')
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('rbcatalog/offer/grid', ['id'=>$this->getRequest()->getParam('id')]);
    }
}
