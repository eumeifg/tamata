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
namespace Magedelight\Vendor\Block\Adminhtml\Vendor;

use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magedelight\Catalog\Model\VendorProduct;

/**
 * @author Rocket Bazaar Core Team
 * Created at 11 April, 2016 06:03:12 PM
 */
class Grid extends WidgetGrid
{
    protected function _construct()
    {
        parent::_construct();
        $this->requestStatus = (int)$this->getRequest()->getParam(
            VendorProduct::STATUS_PARAM_NAME,
            VendorProduct::STATUS_UNLISTED
        );
        $this->setDefaultSort('vendor_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_product_filter');
    }
}
