<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * RMA Adminhtml Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magedelight\Rma\Block\Adminhtml;

/**
 * @api
 * @since 100.0.2
 */
class Rma extends \Magento\Rma\Block\Adminhtml\Rma
{
    /**
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }
    /**
     * Initialize RMA management page
     *
     * @return void
     */
    public function _construct()
    {
        $this->_controller = 'adminhtml_rma';
        $this->_blockGroup = 'Magento_Rma';
        $this->_headerText = __('Returns');
        $this->_addButtonLabel = __('New Return Request');

        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->removeButton('add');
        return parent::_prepareLayout();
    }
}
