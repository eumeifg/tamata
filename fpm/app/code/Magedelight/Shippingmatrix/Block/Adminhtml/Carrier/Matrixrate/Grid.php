<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Shippingmatrix
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Shippingmatrix\Block\Adminhtml\Carrier\Matrixrate;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * @var \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate
     */
    protected $_matrixrate;

    /**
     * @var \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $collectionFactory
     * @param \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $matrixrate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $collectionFactory,
        \Magedelight\Shippingmatrix\Model\Carrier\Matrixrate $matrixrate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_matrixrate = $matrixrate;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shippingMatrixrateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param string $name
     * @return $this
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return \Magedelight\Shippingmatrix\Block\Adminhtml\Carrier\Matrixrate\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Magedelight\Shippingmatrix\Model\ResourceModel\Carrier\Matrixrate\Collection */
        $collection = $this->_collectionFactory->create();
        $collection->setConditionFilter($this->getConditionName())->setWebsiteFilter($this->getWebsiteId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );
        $this->addColumn(
            'dest_city',
            ['header' => __('City'), 'index' => 'dest_city', 'default' => '*']
        );
        $this->addColumn(
            'dest_zip',
            ['header' => __('Zip/Postal Code From'), 'index' => 'dest_zip', 'default' => '*']
        );
        $this->addColumn(
            'dest_zip_to',
            ['header' => __('Zip/Postal Code To'), 'index' => 'dest_zip_to', 'default' => '*']
        );

        $label = $this->_matrixrate->getCode('condition_name_short', $this->getConditionName());

        $this->addColumn(
            'condition_from_value',
            ['header' => $label.__('>='), 'index' => 'condition_from_value']
        );

        $this->addColumn(
            'condition_to_value',
            ['header' => $label.__('<='), 'index' => 'condition_to_value']
        );

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        $this->addColumn(
            'shipping_method',
            ['header' => __('Shipping Method'), 'index' => 'shipping_method']
        );

        $this->addColumn(
            'vendor_id',
            ['header' => __('Vendor'), 'index' => 'vendor_id', 'default' => '*']
        );

        $this->addColumn(
            'province_city',
            ['header' => __('Province City'), 'index' => 'province_city', 'default' => '0']
        );

        $this->addColumn(
            'customer_group_id',
            ['header' => __('Customer Group ID'), 'index' => 'customer_group_id', 'default' => '0']
        );

        return parent::_prepareColumns();
    }
}
