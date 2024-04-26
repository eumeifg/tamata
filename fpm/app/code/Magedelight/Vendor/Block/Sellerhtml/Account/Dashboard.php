<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Vendor
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Vendor\Block\Sellerhtml\Account;

/**
 * @author Rocket Bazaar Core Team
 *  Created at 15 Feb, 2016 10:58:27 AM
 */
class Dashboard extends \Magedelight\Vendor\Block\Sellerhtml\Dashboard\AbstractDashboard
{
    /**
     * @var DashboardManagementInterface
     */
    protected $dashboardManagement;

    /**
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Vendor\Api\DashboardManagementInterface $dashboardManagement
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Vendor\Api\DashboardManagementInterface $dashboardManagement
    ) {
        $this->dashboardManagement = $dashboardManagement;
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        $this->addChild('lastOrders', \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders\Grid::class);

        $this->addChild('sales_summary', \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Orders\Sales::class);

        $this->addChild('products_viewed', \Magedelight\Vendor\Block\Sellerhtml\Dashboard\Products\ProductsViewed::class);

        return parent::_prepareLayout();
    }

    public function getVendorDashboard()
    {
        $result = $this->dashboardManagement->vendorDashboard();
        return $result;
    }
}
