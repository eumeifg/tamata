<?php

namespace CAT\VIP\Block\Adminhtml\VipOffers;

use Magedelight\Catalog\Model\Product as VendorProduct;
use Magento\Config\Model\Config\Source\Yesno;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var VendorProduct
     */
    protected $vipProduct;

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


    protected $type;
    protected $group;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Catalog\Model\ProductFactory $vendorProductFactory
     * @param \Magedelight\Vendor\Model\Vendor $vendorModel
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \CAT\VIP\Model\VIPProductsFactory $vipProductFactory,
        \Magedelight\Vendor\Model\Vendor $vendorModel,
        \Magento\Framework\Registry $coreRegistry,
        \CAT\VIP\Model\DiscountType $type,
        \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider $group,
        array $data = []
    ) {
        $this->vipProduct = $vipProductFactory->create();
        $this->vendorModel = $vendorModel;
        $this->coreRegistry = $coreRegistry;
        $this->type = $type;
        $this->group = $group;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('vendorOfferGrid');
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vip_offer_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->vipProduct->getCollection();
        $collection->addFieldToFilter('product_id', $this->getRequest()->getParam('id'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
            'header' => __('ID'),
            'type' => 'number',
            'index' => 'entity_id',
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
            'ind_qty',
            [
            'header' => __('Individual Qty'),
            'index' => 'ind_qty',
            ]
        );

        $this->addColumn(
            'global_qty',
            [
            'header' => __('Global Qty'),
            'index' => 'global_qty',
            ]
        );

        $this->addColumn('discount_type', [
            'header' => __('Type'),
            'index' => 'discount_type',
            'filter_index' => 'discount_type',
            'type' => 'options',
            'options' => $this->type->getOptionArray()
        ]);

        $this->addColumn(
            'discount',
            [
            'header' => __('Discount'),
            'index' => 'discount',
            ]
        );

        $this->addColumn(
            'customer_group',
            [
            'header' => __('Customer Group'),
            'index' => 'customer_group',
            'filter_index' => false,
            'renderer' => 'CAT\VIP\Block\Adminhtml\VipOffers\CustomerGroup'
            ]
        );



        $this->addColumn(
            'edit',
            [
            'renderer' => \Magedelight\Catalog\Block\Adminhtml\Widget\Grid\Column\Renderer\Action::class,
            'header' => __('Edit'),
            'data_attribute' =>  [
                'mage-init' => [
                    'vendorOffers' => [
                        'url' => 'vip/offer/edit',
                        'mode' => 'edit'
                    ],
                ],
            ]
            ]
        );

        $this->addColumn(
            'edit',
            [
                'renderer' => \CAT\VIP\Block\Adminhtml\Widget\Grid\Column\Renderer\DeleteAction::class,
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
        return $this->getUrl('vip/offer/grid', ['id'=>$this->getRequest()->getParam('id')]);
    }
}
