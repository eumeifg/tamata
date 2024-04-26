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
namespace Magedelight\Sales\Block\Adminhtml\Order\View;

use Magento\Store\Model\ScopeInterface;

class History extends \Magento\Sales\Block\Adminhtml\Order\View\History
{
    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        array $data = []
    ) {
        parent::__construct($context, $salesData, $registry, $adminHelper, $data);
    }

    /**
     *
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
