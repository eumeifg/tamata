<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Sales
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Sales\Block\Sellerhtml\Order\Creditmemo;

/**
 * Creditmemo create
 */
class Create extends \Magedelight\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magedelight\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magedelight_Sales';
        $this->_objectId = 'order_id';
        $this->_controller = 'order_creditmemo';
        $this->_mode = 'create';

        parent::_construct();

        $this->buttonList->remove('delete');
        $this->buttonList->remove('save');
    }

    /**
     * Retrieve creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->getCreditmemo()->getInvoice()) {
            $header = __('New Credit Memo for Invoice #%1', $this->getCreditmemo()->getInvoice()->getIncrementId());
        } else {
            $header = __('New Credit Memo for Order #%1', $this->getCreditmemo()->getOrder()->getRealOrderId());
        }

        return $header;
    }

    /**
     * Get back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view',
            ['order_id' => $this->getCreditmemo() ? $this->getCreditmemo()->getOrderId() : null]
        );
    }
}
