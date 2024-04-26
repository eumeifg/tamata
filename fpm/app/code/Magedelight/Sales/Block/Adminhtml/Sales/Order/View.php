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
namespace Magedelight\Sales\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order as MainOrder;
use Magento\Store\Model\ScopeInterface;

/**
 * @author Rocket Bazaar Core Team
 * Created at 26 May, 2016 03:27:11 PM
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    const CONFIG_PATH_ORDER_AUTO_CONFIRMED = 'vendor_sales/order/auto_confirm';

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('order_cancel');
        $this->buttonList->remove('order_hold');
        $this->buttonList->remove('order_unhold');
        $this->buttonList->remove('order_invoice');
        $this->buttonList->remove('order_ship');
        $this->buttonList->remove('order_creditmemo');
        $this->buttonList->remove('order_edit');

        if ($this->canShowAdminConfirmButton()) {
            $this->buttonList->add(
                'confirm_order',
                [
                    'label' => __('Confirm'),
                    'class' => 'confirm',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getConfirmUrl() . '\')'
                ]
            );
        }
    }

    /**
     * describes that auto confirm is enabled for order by admin
     * @return type
     */
    public function isAutoConfirmEnabled()
    {
        return $this->_scopeConfig->getValue(
            self::CONFIG_PATH_ORDER_AUTO_CONFIRMED,
            ScopeInterface::SCOPE_STORE,
            0
        );
    }

    public function canShowAdminConfirmButton()
    {
        return !($this->getOrder()->getData('is_confirmed', false) ||
            $this->getOrder()->getState() == MainOrder::STATE_CANCELED ||
            $this->getOrder()->getData('is_membership_order') == '1');
    }

    public function getConfirmUrl()
    {
        return $this->getUrl(
            'rbsales/order/confirm',
            [$this->_objectId => $this->getRequest()->getParam($this->_objectId)]
        );
    }

    /**
     * Function to check if Magento order status is visible to customers on order list page.
     * @return bool
     */
    public function isMagentoOrderStatusDisplayed()
    {
        return $this->_scopeConfig->getValue(
            \Magedelight\Sales\Model\Order::IS_MAGENTO_ORDER_STATUS_ALLOWED,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
