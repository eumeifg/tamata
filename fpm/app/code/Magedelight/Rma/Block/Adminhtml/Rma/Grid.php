<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magedelight\Rma\Block\Adminhtml\Rma;

/**
 * RMA Grid
 */
class Grid extends \Magento\Rma\Block\Adminhtml\Rma\Grid
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Rma\Model\RmaFactory $rmaFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Rma\Model\RmaFactory $rmaFactory,
        array $data = array())
    {
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $rmaFactory,
            $data);
    }

    /**
     * Prepare related item collection
     *
     * @return \Magento\Rma\Block\Adminhtml\Rma\Grid
     */
    protected function _prepareCollection()
    {
        $this->_beforePrepareCollection();
        return parent::_prepareCollection();
    }

    /**
     * Configuring and setting collection
     *
     * @return $this
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            /** @var $collection \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
            $collection = $this->_collectionFactory->create();
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Rma\Block\Adminhtml\Rma\Grid
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn(
            'vendor_name',
            [
                'header' => __('Supplier Name'),
                'index' => 'vendor_name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );
        return $this;
    }

    /**
     * Prepare massaction
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Close'),
                'url' => $this->getUrl($this->_getControllerUrl('close')),
                'confirm' => __(
                    'You have chosen to change status(es) of the selected RMA requests to Close.'
                    . ' Are you sure you want to continue?'
                )
            ]
        );

        return $this;
    }

    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return '*/*/' . $action;
    }

    /**
     * Retrieve row url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl($this->_getControllerUrl('edit'), ['id' => $row->getId()]);
    }
}
