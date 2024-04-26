<?php

namespace Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

use Magento\SalesRule\Model\RegistryConstants;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $_salesRuleCoupon;

    /**
     * @var \Cminds\Coupon\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $salesRuleCoupon
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $salesRuleCoupon,
        \Magento\Framework\Registry $coreRegistry,
        \Cminds\Coupon\Model\ResourceModel\Coupon\CollectionFactory $collection,
        array $data = []
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_salesRuleCoupon = $salesRuleCoupon;
        $this->_collectionFactory = $collection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection for grid
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $priceRule = $this->_coreRegistry->registry(RegistryConstants::CURRENT_SALES_RULE);

        $collection = $this->_collectionFactory->create()->addRuleToFilter($priceRule)->addGeneratedCouponsFilter()->addCountedErrors();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Define grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header' => __('Coupon Code'),
            'index' => 'code'
        ));
        $this->addColumn('coupon_used_multiple', array(
            'header' => __('Used more times than it is defined'),
            'index' => 'coupon_used_multiple',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));
        $this->addColumn('coupon_used_multiple_customer_group', array(
            'header' => __('Used more times than it is defined in customer group'),
            'index' => 'coupon_used_multiple_customer_group',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));
        $this->addColumn('coupon_expired', array(
            'header' => __('Used when expired'),
            'index' => 'coupon_expired',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));
        $this->addColumn('coupon_not_apply_rule', array(
            'header' => __('Does not match conditions'),
            'index' => 'coupon_not_apply_rule',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));
        $this->addColumn('customer_not_belong_group', array(
            'header' => __('Used in wrong customer group'),
            'index' => 'customer_not_belong_group',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));
        $this->addColumn('coupon_other_messages', array(
            'header' => __('Default error message'),
            'index' => 'coupon_other_messages',
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter' => false,
        ));

        $this->addColumn('used', array(
            'header' => __('Used'),
            'index' => 'times_used',
            'width' => '100',
            'type' => 'options',
            'options' => array(
                __('No'),
                __('Yes')
            ),
            'renderer' => 'Cminds\Coupon\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used',
            'filter_condition_callback' => [$this->_salesRuleCoupon->create(), 'addIsUsedFilterCallback']
        ));
        $this->addColumn(
            'times_used',
            ['header' => __('Times Used'), 'index' => 'times_used', 'width' => '50', 'type' => 'number']
        );
        $this->addColumn('created_at', array(
            'header' => __('Created On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'align' => 'center',
            'width' => '160'
        ));

        $this->addExportType('*/*/exportCouponsCsv', __('CSV'));
        $this->addExportType('*/*/exportCouponsXml', __('Excel XML'));
        return parent::_prepareColumns();
    }

    /**
     * Configure grid mass actions
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('coupon_id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseAjax(true);
        $this->getMassactionBlock()->setHideFormElement(true);

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('sales_rule/*/couponsMassDelete', ['_current' => true]),
                'confirm' => __('Are you sure you want to delete the selected coupon(s)?'),
                'complete' => 'refreshCouponCodesGrid'
            ]
        );

        return $this;
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales_rule/*/couponsGrid', ['_current' => true]);
    }
}
