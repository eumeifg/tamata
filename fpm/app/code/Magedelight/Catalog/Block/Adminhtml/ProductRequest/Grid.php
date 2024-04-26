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
namespace Magedelight\Catalog\Block\Adminhtml\ProductRequest;

use Magedelight\Catalog\Model\ProductRequest;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var ProductRequest
     */
    protected $productRequest;

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
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productTypes;

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Catalog\Model\ProductRequestFactory $productRequestFactory,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Catalog\Model\Product\Type $productTypes,
        array $data = []
    ) {
        $this->productRequest = $productRequestFactory->create();
        $this->vendorModel = $vendorModel;
        $this->attributeRepository = $attributeRepository;
        $this->systemStore = $systemStore;
        $this->_productTypes = $productTypes;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->requestStatus = (int)$this->getRequest()->getParam(
            ProductRequest::STATUS_PARAM_NAME,
            ProductRequest::STATUS_PENDING
        );
        $this->setId('productRequestGrid');
        $this->setDefaultSort('product_request_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('productrequest_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->productRequest->getCollection(true);
        $collection->getSelect()->joinLeft(
            ['mdvprw' => 'md_vendor_product_request_website'],
            'mdvprw.product_request_id = main_table.product_request_id AND mdvprw.website_id = '
            . $this->_storeManager->getDefaultStoreView()->getWebsiteId(),
            ['price','special_price']
        );
        $collection->getSelect()->joinLeft(
            ['mdvprs' => 'md_vendor_product_request_store'],
            'mdvprs.product_request_id = main_table.product_request_id',
            ['mdvprs.store_id','name']
        );
        $collection->addFieldToFilter('status', ['eq' => $this->requestStatus]);
        $existing = (int)  $this->getRequest()->getParam('existing', 0);
        $collection->addFieldToFilter('is_offered', [['neq' => 2],['null'=> true]]);
        if ($this->requestStatus === ProductRequest::STATUS_PENDING) {
            if ($existing === 1) {
                $collection->addFieldToFilter('marketplace_product_id', ['notnull' => true]);
            } else {
                $collection->addFieldToFilter('marketplace_product_id', ['null' => true]);
            }
        }
        $collection->getSelect()->distinct(true);
        /* Exclude child products. */
        $collection->getSelect()->where(
            'main_table.product_request_id NOT IN (SELECT product_request_id from md_vendor_product_request_super_link)'
        );
        /* Exclude child products. */
        $collection->addFilterToMap('name', 'mdvprs.name');
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
            'product_request_id',
            [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'product_request_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'base_image',
            [
                'header' => __('Image'),
                'class' => 'Image',
                'name' => 'Image',
                'filter' => false,
                'sortable' => false,
                'renderer' => \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer\Image::class,
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
            'name',
            [
            'header' => __('Product Name'),
            'index' => 'name',
            'filter_index' => 'name',
            'renderer' => \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Grid\Renderer\ProductName::class
            ]
        );

        $this->addColumn(
            'type_id',
            [
            'header' => __('Type'),
            'index' => 'type_id',
            'filter_index' => 'type_id',
            'type' => 'options',
            'options' => $this->_productTypes->getOptionArray()
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
            'header' => __('Vendor SKU'),
            'index' => 'vendor_sku',
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
            'currency_code' => $this->_storeManager->getStore()->getBaseCurrency()->getCode(),
            'type' => 'price'
            ]
        );

        $this->addColumn(
            'has_variants',
            [
            'header' => __('Has Variants'),
            'index' => 'has_variants',
            'type' => 'options',
            'options' => $this->productRequest->getHasVariantsBoolean()
            ]
        );

        $this->addColumn(
            'is_requested_for_edit',
            [
            'header' => __('Is Requested For Edit'),
            'index' => 'is_requested_for_edit',
            'type' => 'options',
            'width' => '20px',
            'options' => $this->productRequest->getHasVariantsBoolean()

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
                    'filter_index' => 'website_ids',
                    'index' => 'website_ids',
                    'type' => 'options',
                    'renderer' => \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer\Website::class,
                    'options' => array_combine($keys, $values)
                ]
        );

        $this->addColumn(
            'created_at',
            [
            'header' => __('Created At'),
            'index' => 'created_at',
            'type' => 'time',
            ]
        );

        if (!$this->requestStatus) {
            $this->addColumn(
                'action',
                [
                    'header' => __('Action'),
                    'filter'    => false,
                    'sortable'  => false,
                    'index' => 'stores',
                    'header_css_class' => 'col-action',
                     'column_css_class' => 'col-action',
                    'renderer'  => \Magedelight\Catalog\Block\Adminhtml\ProductRequest\Renderer\Actionlink::class,
                 ]
            );
        }
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
        $this->setMassactionIdField('product_request_id');
        $this->getMassactionBlock()->setFormFieldName('product_request');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('rbcatalog/productrequest/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        return $this;
    }

    /**
     *
     * @return type int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAttributeIdofProductName()
    {
        return $this->attributeRepository->get('catalog_product', 'name')->getId();
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
        $params =  [
                        'store' => $this->productRequest->getDefaultStoreId($row->getWebsiteIds()),
                        'id' => $row->getId(),
                        'status' => $row->getStatus(),
                        'existing' => $this->getRequest()->getParam('existing', 0)
                        ];
        return $this->getUrl(
            '*/*/edit',
            $params
        );
    }
}
