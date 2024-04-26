<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magedelight\Rma\Block\Adminhtml\Order\View;

/**
 * Additional buttons on order view page
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Buttons extends \Magento\Rma\Block\Adminhtml\Order\View\Buttons
{
    const CREATE_RMA_BUTTON_DEFAULT_SORT_ORDER = 35;

    /**
     * Rma data
     *
     * @var \Magento\Rma\Helper\Data
     */
    protected $_rmaData = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param \Magento\Rma\Helper\Data $rmaData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        \Magento\Rma\Helper\Data $rmaData,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $salesConfig,
            $reorderHelper,
            $rmaData,
            $data
        );
        $this->_rmaData = $rmaData;
    }

    /**
     * Add button to Shopping Cart Management etc.
     *
     * @return $this
     */
    public function addButtons()
    {
        if ($this->_isCreateRmaButtonRequired()) {
            $parentBlock = $this->getParentBlock();
            $buttonUrl = $this->_urlBuilder->getUrl(
                'adminhtml/rma/new',
                ['order_id' => $parentBlock->getOrderId()]
            );

            $this->getToolbar()->addChild(
                'create_rma',
                \Magento\Backend\Block\Widget\Button::class,
                ['label' => __('Create Returns'), 'onclick' => 'setLocation(\'' . $buttonUrl . '\')']
            );
        }
        return $this;
    }

    /**
     * Check if 'Create RMA' button has to be displayed
     *
     * @return boolean
     */
    protected function _isCreateRmaButtonRequired()
    {
        return false;
        $parentBlock = $this->getParentBlock();
        return $parentBlock instanceof \Magento\Backend\Block\Template &&
            $parentBlock->getOrderId() &&
            $this->_rmaData->canCreateRma(
                $parentBlock->getOrder(),
                true
            );
    }
}
