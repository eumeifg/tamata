<?php

namespace CAT\GiftCard\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons;

/**
 * Class Grid
 * @package CAT\GiftCard\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var \CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $_giftCardCoupon;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory $giftCardCoupon
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \CAT\GiftCard\Model\ResourceModel\Coupon\CollectionFactory $giftCardCoupon,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_giftCardCoupon = $giftCardCoupon;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('couponCodesGrid');
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        $priceRule = $this->_coreRegistry->registry(\CAT\GiftCard\Model\RegistryConstants::CURRENT_GIFT_CARD_RULE);

        /**
         * @var \CAT\GiftCard\Model\ResourceModel\Coupon\Collection $collection
         */
        $collection = $this->_giftCardCoupon->create()->addRuleToFilter($priceRule)->addGeneratedCouponsFilter();

        if ($this->_isExport && $this->getMassactionBlock()->isAvailable()) {
            $itemIds = $this->getMassactionBlock()->getSelected();
            if (!empty($itemIds)) {
                $collection->addFieldToFilter('coupon_id', ['in' => $itemIds]);
            }
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('code', ['header' => __('Coupon Code'), 'index' => 'code']);

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'align' => 'center',
                'width' => '160'
            ]
        );

        $this->addColumn(
            'used',
            [
                'header' => __('Uses'),
                'index' => 'times_used',
                'width' => '100',
                'type' => 'options',
                'options' => [__('No'), __('Yes')],
                'renderer' =>
                    \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid\Column\Renderer\Used::class,
                'filter_condition_callback' => [$this->_giftCardCoupon->create(), 'addIsUsedFilterCallback']
            ]
        );

        $this->addColumn(
            'times_used',
            ['header' => __('Times Used'), 'index' => 'times_used', 'width' => '50', 'type' => 'number']
        );

        //$this->addExportType('*/*/exportCouponsCsv', __('CSV'));
        //$this->addExportType('*/*/exportCouponsXml', __('Excel XML'));
        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
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
                'url' => $this->getUrl('*/rules_coupon/couponsMassDelete', ['_current' => true]),
                'confirm' => __('Are you sure you want to delete the selected coupon(s)?'),
                /*'complete' => 'refreshCouponCodesGrid'*/
            ]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/rules_coupon/couponsGrid', ['_current' => true]);
    }
}